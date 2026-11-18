<?php
declare(strict_types=1);

namespace Yireo\Avif\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Yireo\Avif\Convertor\GdEncoder;

class AvifSupport extends Field
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        Context $context,
        private readonly GdEncoder $gdEncoder,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(AbstractElement $element): string
    {
        if ($this->gdEncoder->isSupported()) {
            return $this->escape((string)__('Yes, PHP GD supports AVIF'));
        }

        $message = (string)__('No, PHP GD does not support AVIF. Compile GD with libavif to convert images.');
        return '<strong>' . $this->escape($message) . '</strong>';
    }

    private function escape(string $text): string
    {
        /** @var string $escapedText */
        $escapedText = $this->_escaper->escapeHtml($text);
        return $escapedText;
    }
}
