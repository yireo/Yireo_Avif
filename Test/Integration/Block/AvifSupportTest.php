<?php
declare(strict_types=1);

namespace Yireo\Avif\Test\Integration\Block;

use Magento\Framework\Data\Form\Element\Note;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Yireo\Avif\Block\Adminhtml\System\Config\AvifSupport;
use Yireo\Avif\Test\Integration\Stub\FakeGdEncoder;

/**
 * @magentoAppArea adminhtml
 */
class AvifSupportTest extends TestCase
{
    public function testIfSupportedGdIsReported(): void
    {
        $html = $this->render(new FakeGdEncoder(true));
        $this->assertStringContainsString('Yes, PHP GD supports AVIF', $html);
    }

    public function testIfMissingGdSupportIsReported(): void
    {
        $html = $this->render(new FakeGdEncoder(false));
        $this->assertStringContainsString('No, PHP GD does not support AVIF', $html);
    }

    private function render(FakeGdEncoder $gdEncoder): string
    {
        $objectManager = Bootstrap::getObjectManager();
        $block = $objectManager->create(AvifSupport::class, ['gdEncoder' => $gdEncoder]);
        $element = $objectManager->create(Note::class);
        $element->setForm($objectManager->create(\Magento\Framework\Data\Form::class));

        return $block->render($element);
    }
}
