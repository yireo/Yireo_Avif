import {test as baseTest, expect, Page} from '@playwright/test';

const TEST_IMAGES = ['flowers', 'goku', 'transparent-dragon'];

const defaultConfig = {
    'yireo_avif/settings/enabled': 1,
    'yireo_avif/settings/quality_level': 60,
    'yireo_avif/settings/speed': 10,
    'yireo_avif/test/enabled': 1,
    'yireo_nextgenimages/settings/enabled': 1,
    'yireo_nextgenimages/settings/convert_images': 1,
    'yireo_nextgenimages/settings/target_directory': 'same_as_source',
    'yireo_nextgenimages/settings/hash': 0,
    'dev/static/sign': 0,
};

const test = baseTest.extend<{ pageErrors: string[] }>({
    pageErrors: async ({page}, use) => {
        const pageErrors: string[] = [];
        page.on('pageerror', error => pageErrors.push(error.message));
        await use(pageErrors);
    },
});

async function configureAvif(page: Page, config: Record<string, any> = {}) {
    const response = await page.request.post('/loki_functional_tests/index/configure?token=' + process.env.TEST_TOKEN, {
        form: {config: JSON.stringify({config: {...defaultConfig, ...config}})},
    });

    const body = await response.text();
    let data: any;
    try {
        data = JSON.parse(body);
    } catch (error) {
        throw new Error(`Configure endpoint returned no JSON (status ${response.status()}): ${body.slice(0, 500)}`);
    }

    expect(data.error, 'Configure error').toBeUndefined();
}

async function gotoTestPage(page: Page) {
    const url = () => '/avif/test/images?nocache=' + Date.now() + Math.random();

    await page.goto(url());
    const imageUrls = await page.locator('img').evaluateAll(
        (images: HTMLImageElement[]) => images.map(image => image.src)
    );

    for (const imageUrl of imageUrls) {
        if (imageUrl.startsWith(new URL(page.url()).origin)) {
            await page.request.get(imageUrl);
        }
    }

    await page.goto(url());
}

async function getAvifSources(page: Page): Promise<string[]> {
    return page.locator('picture source[type="image/avif"]').evaluateAll(
        (sources: HTMLSourceElement[]) => sources.map(source => source.srcset.trim().split(/\s+/)[0])
    );
}

async function expectImagesToBeLoaded(page: Page) {
    const images = page.locator('img[src*="/Yireo_Avif/images/test/"]');
    await expect(images).toHaveCount(TEST_IMAGES.length);

    for (const image of await images.all()) {
        await expect.poll(
            () => image.evaluate((element: HTMLImageElement) => element.complete ? element.naturalWidth : 0),
            {message: 'Image is loaded: ' + await image.getAttribute('src')}
        ).toBeGreaterThan(0);
    }
}

test.describe('AVIF images', function () {
    test('only adds AVIF sources that point to real AVIF files', async function ({page, pageErrors}) {
        await configureAvif(page);
        await gotoTestPage(page);

        for (const avifUrl of await getAvifSources(page)) {
            expect(avifUrl).toMatch(/\.avif$/);
            const response = await page.request.get(avifUrl);
            expect(response.status(), `Status of ${avifUrl}`).toBe(200);
            const fileType = (await response.body()).subarray(4, 12).toString('latin1');
            expect(fileType, `File type of ${avifUrl}`).toBe('ftypavif');
        }

        await expectImagesToBeLoaded(page);
        expect(pageErrors).toEqual([]);
    });

    test('lets the browser pick the AVIF image as first source', async function ({page}) {
        await configureAvif(page);
        await gotoTestPage(page);

        const avifSources = await getAvifSources(page);
        test.skip(avifSources.length === 0, 'PHP GD on the server does not support AVIF');
        expect(avifSources).toHaveLength(TEST_IMAGES.length);

        for (const imageName of TEST_IMAGES) {
            const image = page.locator(`picture img[src*="/${imageName}."]`);
            const firstSource = image.locator('xpath=ancestor::picture[1]').locator('source').first();
            await expect(firstSource).toHaveAttribute('type', 'image/avif');
            await expect.poll(
                () => image.evaluate((element: HTMLImageElement) => element.complete ? element.currentSrc : ''),
                {message: `Browser picks the AVIF image for "${imageName}"`}
            ).toMatch(new RegExp(`/${imageName}\\.avif$`));
        }
    });

    test('does not add AVIF sources when disabled', async function ({page}) {
        await configureAvif(page, {'yireo_avif/settings/enabled': 0});

        const avifRequests: string[] = [];
        page.on('request', request => {
            if (request.url().endsWith('.avif')) {
                avifRequests.push(request.url());
            }
        });

        await gotoTestPage(page);

        expect(await getAvifSources(page)).toHaveLength(0);
        expect(avifRequests).toEqual([]);
        await expectImagesToBeLoaded(page);
    });
});
