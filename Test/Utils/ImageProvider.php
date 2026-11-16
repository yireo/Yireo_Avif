<?php
declare(strict_types=1);

namespace Yireo\Avif\Test\Utils;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class ImageProvider implements ArgumentInterface
{
    /**
     * @return string[]
     */
    public function getImages(): array
    {
        return [
            'images/test/flowers.jpg',
            'images/test/goku.jpg',
            'images/test/transparent-dragon.png',
        ];
    }
}
