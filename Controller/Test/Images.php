<?php
declare(strict_types=1);

namespace Yireo\Avif\Controller\Test;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Images implements HttpGetActionInterface
{
    public function __construct(
        private readonly PageFactory $pageFactory
    ) {
    }

    public function execute(): Page
    {
        $page = $this->pageFactory->create();
        $page->addHandle('avif_test_images');

        return $page;
    }
}
