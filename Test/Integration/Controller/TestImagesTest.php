<?php
declare(strict_types=1);

namespace Yireo\Avif\Test\Integration\Controller;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\TestFramework\TestCase\AbstractController;
use Yireo\Avif\Convertor\GdEncoder;
use Yireo\Avif\Test\Integration\Stub\FakeGdEncoder;

/**
 * @magentoAppArea frontend
 */
class TestImagesTest extends AbstractController
{
    private const IMAGES = ['flowers.jpg', 'goku.jpg', 'transparent-dragon.png'];

    private string $staticImageFolder;

    protected function setUp(): void
    {
        parent::setUp();
        $root = $this->_objectManager->get(DirectoryList::class)->getRoot();
        $this->staticImageFolder = $root . '/pub/static/frontend/Magento/luma/en_US/Yireo_Avif/images/test';
        if (!is_dir($this->staticImageFolder)) {
            mkdir($this->staticImageFolder, 0777, true);
        }

        $this->removeStaticImages();
        foreach (self::IMAGES as $image) {
            $targetPath = $this->staticImageFolder . '/' . $image;
            copy(dirname(__DIR__, 3) . '/view/frontend/web/images/test/' . $image, $targetPath);
            touch($targetPath, time() - 100);
        }
    }

    protected function tearDown(): void
    {
        $this->_objectManager->removeSharedInstance(GdEncoder::class);
        $this->removeStaticImages();
        parent::tearDown();
    }

    private function getResponseBody(): string
    {
        $response = $this->getResponse();
        $this->assertInstanceOf(HttpResponse::class, $response);

        return (string)$response->getBody();
    }

    private function removeStaticImages(): void
    {
        foreach (glob($this->staticImageFolder . '/*') ?: [] as $file) {
            unlink($file);
        }
    }

    public function testIfImagesAreWrappedInPictureWithAvifSource(): void
    {
        $this->_objectManager->addSharedInstance(new FakeGdEncoder(), GdEncoder::class);
        $this->dispatch('avif/test/images');

        $body = $this->getResponseBody();
        foreach (['flowers', 'goku', 'transparent-dragon'] as $imageName) {
            $pattern = '#<picture>\s*<source\s+type="image/avif"\s+srcset="[^"]+/Yireo_Avif/images/test/'
                . $imageName . '\.avif"#';
            $this->assertMatchesRegularExpression($pattern, $body);
        }
    }

    /**
     * @magentoConfigFixture current_store yireo_avif/settings/enabled 0
     */
    public function testIfNoAvifSourceIsAddedWhenDisabled(): void
    {
        $this->_objectManager->addSharedInstance(new FakeGdEncoder(), GdEncoder::class);
        $this->dispatch('avif/test/images');

        $body = $this->getResponseBody();
        $this->assertStringContainsString('/Yireo_Avif/images/test/flowers.jpg', $body);
        $this->assertStringNotContainsString('image/avif', $body);
    }

    public function testIfNoAvifSourceIsAddedWhenGdLacksAvifSupport(): void
    {
        $this->_objectManager->addSharedInstance(new FakeGdEncoder(false), GdEncoder::class);
        $this->dispatch('avif/test/images');

        $body = $this->getResponseBody();
        $this->assertStringContainsString('/Yireo_Avif/images/test/flowers.jpg', $body);
        $this->assertStringNotContainsString('image/avif', $body);
        $this->assertStringNotContainsString('.avif', $body);
    }
}
