<?php
declare(strict_types=1);

namespace Yireo\Avif\Test\Unit\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yireo\Avif\Config\Config;
use Yireo\NextGenImages\Config\Config as NextGenImagesConfig;

class ConfigTest extends TestCase
{
    public function testEnabledReadsValueFromCurrentStore(): void
    {
        $store = $this->createMock(StoreInterface::class);
        $store->method('getId')->willReturn(3);
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('yireo_avif/settings/enabled', ScopeInterface::SCOPE_STORE, 3)
            ->willReturn('1');

        $this->assertTrue($this->getConfig($scopeConfig, $store)->enabled());
    }

    public function testEnabledIsFalseWithoutStore(): void
    {
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->method('getStore')->willThrowException(new NoSuchEntityException());
        $config = new Config(
            $this->createMock(ScopeConfigInterface::class),
            $storeManager,
            $this->createMock(NextGenImagesConfig::class)
        );

        $this->assertFalse($config->enabled());
    }

    public function testAllowImageCreationComesFromNextGenImages(): void
    {
        $nextGenImagesConfig = $this->createMock(NextGenImagesConfig::class);
        $nextGenImagesConfig->method('allowImageCreation')->willReturn(false);
        $config = new Config(
            $this->createMock(ScopeConfigInterface::class),
            $this->createMock(StoreManagerInterface::class),
            $nextGenImagesConfig
        );

        $this->assertFalse($config->allowImageCreation());
    }

    #[DataProvider('getQualityLevels')]
    public function testQualityLevelIsBetween0And100(mixed $value, int $expected): void
    {
        $this->assertSame($expected, $this->getConfigWithValue($value)->getQualityLevel());
    }

    /**
     * @return array<array{mixed, int}>
     */
    public static function getQualityLevels(): array
    {
        return [
            ['60', 60],
            ['0', 0],
            ['100', 100],
            ['101', 100],
            ['-5', 0],
            [null, 0],
        ];
    }

    #[DataProvider('getSpeeds')]
    public function testSpeedIsBetween0And10(mixed $value, int $expected): void
    {
        $this->assertSame($expected, $this->getConfigWithValue($value)->getSpeed());
    }

    /**
     * @return array<array{mixed, int}>
     */
    public static function getSpeeds(): array
    {
        return [
            ['6', 6],
            ['0', 0],
            ['10', 10],
            ['11', 10],
            ['-1', 0],
        ];
    }

    private function getConfigWithValue(mixed $value): Config
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturn($value);

        return $this->getConfig($scopeConfig, $this->createMock(StoreInterface::class));
    }

    private function getConfig(ScopeConfigInterface $scopeConfig, StoreInterface $store): Config
    {
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($store);

        return new Config($scopeConfig, $storeManager, $this->createMock(NextGenImagesConfig::class));
    }
}
