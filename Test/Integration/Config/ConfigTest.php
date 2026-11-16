<?php
declare(strict_types=1);

namespace Yireo\Avif\Test\Integration\Config;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Yireo\Avif\Config\Config;

class ConfigTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $config = Bootstrap::getObjectManager()->create(Config::class);
        $this->assertTrue($config->enabled());
        $this->assertSame(60, $config->getQualityLevel());
        $this->assertSame(6, $config->getSpeed());
    }

    /**
     * @magentoConfigFixture current_store yireo_avif/settings/enabled 0
     * @magentoConfigFixture current_store yireo_avif/settings/quality_level 85
     * @magentoConfigFixture current_store yireo_avif/settings/speed 2
     */
    public function testStoreValues(): void
    {
        $config = Bootstrap::getObjectManager()->create(Config::class);
        $this->assertFalse($config->enabled());
        $this->assertSame(85, $config->getQualityLevel());
        $this->assertSame(2, $config->getSpeed());
    }

    /**
     * @magentoConfigFixture current_store yireo_nextgenimages/settings/convert_images 0
     */
    public function testImageCreationFollowsNextGenImages(): void
    {
        $this->assertFalse(Bootstrap::getObjectManager()->create(Config::class)->allowImageCreation());
    }
}
