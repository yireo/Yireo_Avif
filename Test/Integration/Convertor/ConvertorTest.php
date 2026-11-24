<?php
declare(strict_types=1);

namespace Yireo\Avif\Test\Integration\Convertor;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Lock\LockManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;
use Yireo\Avif\Convertor\Convertor;
use Yireo\Avif\Convertor\GdEncoder;
use Yireo\Avif\Test\Integration\Stub\FakeGdEncoder;
use Yireo\NextGenImages\Exception\ConvertorException;
use Yireo\NextGenImages\Image\Image;
use Yireo\NextGenImages\Image\ImageFactory;

/**
 * @magentoAppArea frontend
 */
class ConvertorTest extends TestCase
{
    private ObjectManager $objectManager;
    private string $imageFolder;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->assertInstanceOf(ObjectManager::class, $objectManager);
        $this->objectManager = $objectManager;
        $mediaDirectory = $this->objectManager->get(Filesystem::class)->getDirectoryWrite(DirectoryList::MEDIA);
        $mediaDirectory->delete('yireo_avif_test');
        $mediaDirectory->create('yireo_avif_test');
        $this->imageFolder = $mediaDirectory->getAbsolutePath('yireo_avif_test');

        foreach (['flowers.jpg', 'goku.jpg', 'transparent-dragon.png'] as $imageName) {
            $targetPath = $this->imageFolder . '/' . $imageName;
            copy(dirname(__DIR__, 3) . '/view/frontend/web/images/test/' . $imageName, $targetPath);
            touch($targetPath, time() - 100);
        }
    }

    protected function tearDown(): void
    {
        $this->objectManager->removeSharedInstance(GdEncoder::class);
    }

    /**
     * @magentoConfigFixture current_store yireo_avif/settings/quality_level 75
     * @magentoConfigFixture current_store yireo_avif/settings/speed 8
     */
    public function testIfJpegAndPngImagesAreConvertedToAvif(): void
    {
        $gdEncoder = $this->useGdEncoder(new FakeGdEncoder());

        foreach (['flowers.jpg', 'goku.jpg', 'transparent-dragon.png'] as $imageName) {
            $avifImage = $this->getConvertor()->convertImage($this->getImage($imageName));

            $expectedPath = $this->imageFolder . '/' . pathinfo($imageName, PATHINFO_FILENAME) . '.avif';
            $this->assertSame($expectedPath, $avifImage->getPath());
            $this->assertFileExists($avifImage->getPath());
            $this->assertStringEndsWith('/yireo_avif_test/' . basename($expectedPath), $avifImage->getUrl());
            $this->assertSame('image/avif', $avifImage->getMimetype());
        }

        $this->assertCount(3, $gdEncoder->calls);
        $this->assertSame(75, $gdEncoder->calls[0][2], 'Quality level');
        $this->assertSame(8, $gdEncoder->calls[0][3], 'Speed');
    }

    public function testIfUpToDateAvifImageIsNotConvertedAgain(): void
    {
        $gdEncoder = $this->useGdEncoder(new FakeGdEncoder());
        $avifPath = $this->imageFolder . '/flowers.avif';
        file_put_contents($avifPath, 'existing');
        touch($avifPath, time() + 10);

        $avifImage = $this->getConvertor()->convertImage($this->getImage('flowers.jpg'));

        $this->assertSame($avifPath, $avifImage->getPath());
        $this->assertSame([], $gdEncoder->calls);
        $this->assertSame('existing', file_get_contents($avifPath));
    }

    public function testIfOutdatedAvifImageIsConvertedAgain(): void
    {
        $gdEncoder = $this->useGdEncoder(new FakeGdEncoder());
        $avifPath = $this->imageFolder . '/flowers.avif';
        file_put_contents($avifPath, 'outdated');
        touch($avifPath, time() - 200);

        $this->getConvertor()->convertImage($this->getImage('flowers.jpg'));

        $this->assertCount(1, $gdEncoder->calls);
        $this->assertNotSame('outdated', file_get_contents($avifPath));
    }

    /**
     * @magentoConfigFixture current_store yireo_avif/settings/enabled 0
     */
    public function testIfConversionFailsWhenDisabled(): void
    {
        $gdEncoder = $this->useGdEncoder(new FakeGdEncoder());

        $this->expectExceptionObject(new ConvertorException('AVIF conversion is not enabled'));
        try {
            $this->getConvertor()->convertImage($this->getImage('flowers.jpg'));
        } finally {
            $this->assertSame([], $gdEncoder->calls);
        }
    }

    /**
     * @magentoConfigFixture current_store yireo_nextgenimages/settings/convert_images 0
     */
    public function testIfConversionFailsWhenImageCreationIsNotAllowed(): void
    {
        $gdEncoder = $this->useGdEncoder(new FakeGdEncoder());

        $this->expectExceptionObject(new ConvertorException('AVIF image creation is not allowed'));
        try {
            $this->getConvertor()->convertImage($this->getImage('flowers.jpg'));
        } finally {
            $this->assertSame([], $gdEncoder->calls);
        }
    }

    public function testIfUnsupportedMimetypeIsRejected(): void
    {
        $this->useGdEncoder(new FakeGdEncoder());
        $gifPath = $this->imageFolder . '/animation.gif';
        file_put_contents($gifPath, 'GIF89a');

        $this->expectExceptionObject(new ConvertorException('The image "animation.gif" is not supported'));
        $this->getConvertor()->convertImage(new Image($gifPath, '/media/yireo_avif_test/animation.gif'));
    }

    public function testIfNonImageFileIsRejected(): void
    {
        $gdEncoder = $this->useGdEncoder(new FakeGdEncoder());
        $phpPath = $this->imageFolder . '/script.php';
        file_put_contents($phpPath, '<?php');

        $this->expectExceptionObject(new ConvertorException('The image "script.php" is not supported'));
        try {
            $this->getConvertor()->convertImage(new Image($phpPath, '/media/yireo_avif_test/script.php'));
        } finally {
            $this->assertSame([], $gdEncoder->calls);
        }
    }

    public function testIfPathTraversalIsRejected(): void
    {
        $gdEncoder = $this->useGdEncoder(new FakeGdEncoder());
        $path = $this->imageFolder . '/../yireo_avif_test/flowers.jpg';

        $this->expectExceptionObject(new ConvertorException('The image "flowers.jpg" is not supported'));
        try {
            $this->getConvertor()->convertImage(new Image($path, '/media/yireo_avif_test/flowers.jpg'));
        } finally {
            $this->assertSame([], $gdEncoder->calls);
        }
    }

    public function testIfImageOutsideOfPublicFoldersIsRejected(): void
    {
        $gdEncoder = $this->useGdEncoder(new FakeGdEncoder());
        $root = $this->objectManager->get(DirectoryList::class)->getRoot();
        $path = $root . '/app/etc/yireo-avif-test.png';

        $this->expectExceptionObject(new ConvertorException('The image "yireo-avif-test.png" is not supported'));
        try {
            $this->getConvertor()->convertImage(new Image($path, '/app/etc/yireo-avif-test.png'));
        } finally {
            $this->assertSame([], $gdEncoder->calls);
        }
    }

    public function testIfSrcSetWithInjectedDescriptorIsRejected(): void
    {
        $gdEncoder = $this->useGdEncoder(new FakeGdEncoder());
        $flowersUrl = $this->getImage('flowers.jpg')->getUrl();
        $image = new Image(
            $this->imageFolder . '/flowers.jpg',
            $flowersUrl,
            $flowersUrl . ' 1x"><svg/onload=alert(1)>'
        );

        $this->expectExceptionObject(new ConvertorException('Invalid srcset descriptor'));
        try {
            $this->getConvertor()->convertImage($image);
        } finally {
            $this->assertCount(1, $gdEncoder->calls);
        }
    }

    public function testIfSrcSetWithRemoteImageThrowsConvertorException(): void
    {
        $this->useGdEncoder(new FakeGdEncoder());
        $flowersUrl = $this->getImage('flowers.jpg')->getUrl();
        $image = new Image(
            $this->imageFolder . '/flowers.jpg',
            $flowersUrl,
            $flowersUrl . ' 1x, https://cdn.example.org/flowers.jpg 2x'
        );

        $this->expectException(ConvertorException::class);
        $this->getConvertor()->convertImage($image);
    }

    public function testIfMissingSourceImageIsRejected(): void
    {
        $this->useGdEncoder(new FakeGdEncoder());
        $path = $this->imageFolder . '/missing.jpg';

        $this->expectExceptionObject(new ConvertorException('Source image does not exist: missing.jpg'));
        $this->getConvertor()->convertImage(new Image($path, '/media/yireo_avif_test/missing.jpg'));
    }

    public function testIfConversionFailsWhenGdLacksAvifSupport(): void
    {
        $this->useGdEncoder(new FakeGdEncoder(false));

        $this->expectExceptionObject(new ConvertorException('PHP GD does not support AVIF'));
        $this->getConvertor()->convertImage($this->getImage('flowers.jpg'));
    }

    public function testIfConversionFailsWhenNoAvifFileIsWritten(): void
    {
        $this->useGdEncoder(new FakeGdEncoder(true, false));

        $this->expectExceptionObject(
            new ConvertorException('AVIF path "flowers.avif" does not exist after conversion')
        );
        $this->getConvertor()->convertImage($this->getImage('flowers.jpg'));
    }

    public function testIfSrcSetIsConvertedToAvif(): void
    {
        $gdEncoder = $this->useGdEncoder(new FakeGdEncoder());
        $flowersUrl = $this->getImage('flowers.jpg')->getUrl();
        $gokuUrl = $this->getImage('goku.jpg')->getUrl();
        $srcSetPaths = $this->copyToPathsOfUrls([$flowersUrl => 'flowers.jpg', $gokuUrl => 'goku.jpg']);
        $image = new Image(
            $this->imageFolder . '/flowers.jpg',
            $flowersUrl,
            $flowersUrl . ' 1x, ' . $gokuUrl . ' 2x'
        );

        try {
            $avifImage = $this->getConvertor()->convertImage($image);

            $expectedSrcSet = str_replace('.jpg', '.avif', $flowersUrl . ' 1x, ' . $gokuUrl . ' 2x');
            $this->assertSame($expectedSrcSet, $avifImage->getSrcSet());
            $this->assertFileExists(str_replace('.jpg', '.avif', $srcSetPaths[1]));
            $this->assertCount(3, $gdEncoder->calls);
        } finally {
            foreach ($srcSetPaths as $srcSetPath) {
                @unlink($srcSetPath);
                @unlink(str_replace('.jpg', '.avif', $srcSetPath));
            }
        }
    }

    public function testIfImageIsNotConvertedWhileLocked(): void
    {
        $gdEncoder = $this->useGdEncoder(new FakeGdEncoder());
        $lockManager = $this->createMock(LockManagerInterface::class);
        $lockManager->method('lock')->willReturn(false);
        $lockManager->expects($this->never())->method('unlock');
        $convertor = $this->objectManager->create(Convertor::class, ['lockManager' => $lockManager]);

        $this->expectExceptionObject(new ConvertorException('AVIF image "flowers.avif" is being converted'));
        try {
            $convertor->convertImage($this->getImage('flowers.jpg'));
        } finally {
            $this->assertSame([], $gdEncoder->calls);
        }
    }

    public function testIfLockIsReleasedAfterConversion(): void
    {
        $this->useGdEncoder(new FakeGdEncoder());
        $avifPath = $this->imageFolder . '/flowers.avif';
        $lockName = 'yireo_avif_' . md5($avifPath);
        $lockManager = $this->createMock(LockManagerInterface::class);
        $lockManager->expects($this->once())->method('lock')->with($lockName, 0)->willReturn(true);
        $lockManager->expects($this->once())->method('unlock')->with($lockName)->willReturn(true);
        $convertor = $this->objectManager->create(Convertor::class, ['lockManager' => $lockManager]);

        $convertor->convertImage($this->getImage('flowers.jpg'));
        $this->assertFileExists($avifPath);
    }

    public function testIfRealGdEncoderCreatesAvifImages(): void
    {
        if (!function_exists('imageavif')) {
            $this->markTestSkipped('PHP GD does not support AVIF');
        }

        $avifImage = $this->getConvertor()->convertImage($this->getImage('transparent-dragon.png'));
        $this->assertSame('image/avif', mime_content_type($avifImage->getPath()));
    }

    /**
     * @param array<string, string> $imageNamesByUrl
     * @return string[]
     */
    private function copyToPathsOfUrls(array $imageNamesByUrl): array
    {
        $paths = [];
        foreach ($imageNamesByUrl as $url => $imageName) {
            $path = $this->objectManager->get(ImageFactory::class)->createFromUrl($url)->getPath();
            if (!is_dir(dirname($path))) {
                mkdir(dirname($path), 0777, true);
            }

            copy($this->imageFolder . '/' . $imageName, $path);
            touch($path, time() - 100);
            $paths[] = $path;
        }

        return $paths;
    }

    private function useGdEncoder(FakeGdEncoder $gdEncoder): FakeGdEncoder
    {
        $this->objectManager->addSharedInstance($gdEncoder, GdEncoder::class);

        return $gdEncoder;
    }

    private function getConvertor(): Convertor
    {
        return $this->objectManager->create(Convertor::class);
    }

    private function getImage(string $imageName): Image
    {
        return $this->objectManager->get(ImageFactory::class)->createFromPath($this->imageFolder . '/' . $imageName);
    }
}
