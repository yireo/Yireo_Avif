<?php
declare(strict_types=1);

namespace Yireo\Avif\Test\Integration;

use Magento\Framework\Acl\AclResource\ProviderInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Yireo\Avif\Convertor\Convertor;
use Yireo\NextGenImages\Block\Adminhtml\System\Config\ModuleVersion;
use Yireo\NextGenImages\Convertor\ConvertorListing;

class DiConfigurationTest extends TestCase
{
    public function testIfConvertorIsAddedToConvertorListing(): void
    {
        $convertorListing = Bootstrap::getObjectManager()->create(ConvertorListing::class);
        $avifConvertors = array_filter(
            $convertorListing->getConvertors(),
            fn ($convertor) => $convertor instanceof Convertor
        );
        $this->assertCount(1, $avifConvertors);
    }

    public function testIfAvifConvertorComesFirstSoThatBrowsersPreferAvif(): void
    {
        $convertors = Bootstrap::getObjectManager()->create(ConvertorListing::class)->getConvertors();
        $this->assertInstanceOf(Convertor::class, reset($convertors));
    }

    public function testIfModuleVersionVirtualTypeCanBeCreated(): void
    {
        $block = Bootstrap::getObjectManager()->create(
            'Yireo\Avif\VirtualType\Block\Adminhtml\System\Config\ModuleVersion'
        );
        $this->assertInstanceOf(ModuleVersion::class, $block);
        $this->assertSame((string)$this->getComposerData()['version'], $block->getModuleVersion());
    }

    public function testIfAclResourceExists(): void
    {
        $aclResources = Bootstrap::getObjectManager()->get(ProviderInterface::class)->getAclResources();
        $this->assertContains('Yireo_Avif::config', $this->getResourceIds($aclResources));
    }

    /**
     * @param array<int, array<string, mixed>> $resources
     * @return string[]
     */
    private function getResourceIds(array $resources): array
    {
        $ids = [];
        foreach ($resources as $resource) {
            $ids[] = $resource['id'];
            $ids = array_merge($ids, $this->getResourceIds($resource['children'] ?? []));
        }

        return $ids;
    }

    /**
     * @return array<string, mixed>
     */
    private function getComposerData(): array
    {
        return json_decode((string)file_get_contents(dirname(__DIR__, 2) . '/composer.json'), true);
    }
}
