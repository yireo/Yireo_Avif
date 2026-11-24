<?php
declare(strict_types=1);

namespace Yireo\Avif\Test\Unit\Convertor;

use Magento\Framework\App\Filesystem\DirectoryList;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yireo\Avif\Convertor\ImageGuard;
use Yireo\NextGenImages\Config\Config as NextGenImagesConfig;

class ImageGuardTest extends TestCase
{
    #[DataProvider('getSourcePaths')]
    public function testIsSupportedSource(string $path, bool $expected): void
    {
        $this->assertSame($expected, $this->getImageGuard()->isSupportedSource($path));
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public static function getSourcePaths(): array
    {
        return [
            'media jpg' => ['/var/www/pub/media/catalog/product/a.jpg', true],
            'media jpeg uppercase' => ['/var/www/pub/media/catalog/product/a.JPEG', true],
            'custom media folder' => ['/srv/media/catalog/product/a.png', true],
            'static png' => ['/var/www/pub/static/frontend/Magento/luma/en_US/a.png', true],
            'tmp png' => ['/var/www/var/tmp/pub/media/a.png', true],
            'gif' => ['/var/www/pub/media/a.gif', false],
            'php' => ['/var/www/pub/media/a.php', false],
            'png in middle' => ['/var/www/pub/media/a.png.php', false],
            'no extension' => ['/var/www/pub/media/a', false],
            'outside allowed folders' => ['/var/www/app/etc/a.png', false],
            'other pub folder' => ['/var/www/pub/errors/a.png', false],
            'parent traversal' => ['/var/www/pub/media/../../var/import/a.png', false],
            'current folder segment' => ['/var/www/pub/media/./a.png', false],
            'folder with same prefix' => ['/var/www/pub/media-private/a.png', false],
            'relative path' => ['pub/media/a.png', false],
            'null byte' => ["/var/www/pub/media/a\0.png", false],
        ];
    }

    #[DataProvider('getTargetPaths')]
    public function testIsAllowedTarget(string $path, bool $expected): void
    {
        $this->assertSame($expected, $this->getImageGuard()->isAllowedTarget($path));
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public static function getTargetPaths(): array
    {
        return [
            'media avif' => ['/var/www/pub/media/catalog/product/a.avif', true],
            'cache directory' => ['/var/www/pub/nextgen/catalog/a.avif', true],
            'not avif' => ['/var/www/pub/media/a.php', false],
            'outside allowed folders' => ['/var/www/pub/errors/a.avif', false],
            'parent traversal' => ['/var/www/pub/media/nextgenimages/../../../var/a.avif', false],
        ];
    }

    #[DataProvider('getDescriptors')]
    public function testIsValidSrcSetDescriptor(string $descriptor, bool $expected): void
    {
        $this->assertSame($expected, $this->getImageGuard()->isValidSrcSetDescriptor($descriptor));
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public static function getDescriptors(): array
    {
        return [
            'empty' => ['', true],
            'density' => ['2x', true],
            'decimal density' => ['1.5x', true],
            'width' => ['480w', true],
            'unit only' => ['x', false],
            'unknown unit' => ['480h', false],
            'html injection' => ['1x"><svg/onload=alert(1)>', false],
            'attribute injection' => ["1x'onerror='alert(1)", false],
            'trailing newline' => ["1x\n", false],
        ];
    }

    #[DataProvider('getUrls')]
    public function testIsValidUrl(string $url, bool $expected): void
    {
        $this->assertSame($expected, $this->getImageGuard()->isValidUrl($url));
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public static function getUrls(): array
    {
        return [
            'absolute url' => ['https://example.com/media/a.avif', true],
            'relative url' => ['/media/a.avif', true],
            'encoded entity' => ['/media/a&amp;b.avif', true],
            'empty' => ['', false],
            'double quote' => ['/media/a".avif', false],
            'single quote' => ["/media/a'.avif", false],
            'tag' => ['/media/<svg>.avif', false],
            'space' => ['/media/a b.avif', false],
            'comma' => ['/media/a,b.avif', false],
            'backtick' => ['/media/a`.avif', false],
        ];
    }

    private function getImageGuard(): ImageGuard
    {
        $directoryList = $this->createMock(DirectoryList::class);
        $directoryList->method('getRoot')->willReturn('/var/www');
        $directoryList->method('getPath')->willReturnMap([
            [DirectoryList::MEDIA, '/srv/media'],
            [DirectoryList::STATIC_VIEW, '/var/www/pub/static'],
            [DirectoryList::TMP, '/var/www/var/tmp'],
        ]);

        $nextGenImagesConfig = $this->createMock(NextGenImagesConfig::class);
        $nextGenImagesConfig->method('getCacheDirectoryPath')->willReturn('/var/www/pub/nextgen/');

        return new ImageGuard($directoryList, $nextGenImagesConfig);
    }
}
