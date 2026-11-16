<?php
declare(strict_types=1);

namespace Yireo\Avif\Test\Unit\Convertor;

use PHPUnit\Framework\TestCase;
use Yireo\Avif\Convertor\GdEncoder;
use Yireo\NextGenImages\Exception\ConvertorException;

class GdEncoderTest extends TestCase
{
    private string $tmpDir;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/yireo-avif-' . uniqid();
        mkdir($this->tmpDir);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->tmpDir . '/*') ?: []);
        rmdir($this->tmpDir);
    }

    public function testIsSupportedMatchesGd(): void
    {
        $this->assertSame(function_exists('imageavif'), (new GdEncoder())->isSupported());
    }

    public function testEncodeFailsWhenGdLacksAvifSupport(): void
    {
        if (function_exists('imageavif')) {
            $this->markTestSkipped('PHP GD supports AVIF');
        }

        $this->expectException(ConvertorException::class);
        $this->expectExceptionMessage('PHP GD does not support AVIF');
        (new GdEncoder())->encode($this->getImagePath('flowers.jpg'), $this->tmpDir . '/flowers.avif', 60, 6);
    }

    public function testEncodeFailsForUnreadableImage(): void
    {
        $this->skipWithoutAvifSupport();
        $sourcePath = $this->tmpDir . '/broken.jpg';
        file_put_contents($sourcePath, 'not an image');

        $this->expectException(ConvertorException::class);
        $this->expectExceptionMessage('Unable to read image "' . $sourcePath . '" with GD');
        (new GdEncoder())->encode($sourcePath, $this->tmpDir . '/broken.avif', 60, 6);
    }

    public function testEncodeCreatesAvifFromJpegAndPng(): void
    {
        $this->skipWithoutAvifSupport();

        foreach (['flowers.jpg', 'goku.jpg', 'transparent-dragon.png'] as $imageName) {
            $targetPath = $this->tmpDir . '/' . pathinfo($imageName, PATHINFO_FILENAME) . '.avif';
            (new GdEncoder())->encode($this->getImagePath($imageName), $targetPath, 60, 10);

            $this->assertFileExists($targetPath);
            $this->assertSame('image/avif', mime_content_type($targetPath));
        }
    }

    public function testEncodeKeepsPngTransparency(): void
    {
        $this->skipWithoutAvifSupport();
        $sourcePath = $this->tmpDir . '/transparent.png';
        $pngImage = imagecreatetruecolor(16, 16);
        imagealphablending($pngImage, false);
        imagesavealpha($pngImage, true);
        imagefill($pngImage, 0, 0, (int)imagecolorallocatealpha($pngImage, 0, 0, 0, 127));
        imagepng($pngImage, $sourcePath);

        $targetPath = $this->tmpDir . '/transparent.avif';
        (new GdEncoder())->encode($sourcePath, $targetPath, 60, 10);

        $avifImage = imagecreatefromavif($targetPath);
        $this->assertInstanceOf(\GdImage::class, $avifImage);
        $alpha = (imagecolorat($avifImage, 0, 0) >> 24) & 0x7F;
        $this->assertGreaterThan(0, $alpha);
    }

    private function skipWithoutAvifSupport(): void
    {
        if (!function_exists('imageavif')) {
            $this->markTestSkipped('PHP GD does not support AVIF');
        }
    }

    private function getImagePath(string $imageName): string
    {
        return dirname(__DIR__, 3) . '/view/frontend/web/images/test/' . $imageName;
    }
}
