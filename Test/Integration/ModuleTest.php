<?php
declare(strict_types=1);

namespace Yireo\Avif\Test\Integration;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Module\ModuleList;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class ModuleTest extends TestCase
{
    public function testIfModuleIsRegistered(): void
    {
        $paths = (new ComponentRegistrar())->getPaths(ComponentRegistrar::MODULE);
        $this->assertArrayHasKey('Yireo_Avif', $paths);
    }

    public function testIfModuleIsKnownAndEnabled(): void
    {
        $moduleList = Bootstrap::getObjectManager()->create(ModuleList::class);
        $this->assertTrue($moduleList->has('Yireo_NextGenImages'));
        $this->assertTrue($moduleList->has('Yireo_Avif'));
    }
}
