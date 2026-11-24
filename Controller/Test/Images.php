<?php
declare(strict_types=1);

namespace Yireo\Avif\Controller\Test;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Yireo\Avif\Config\Config;

class Images implements HttpGetActionInterface
{
    public function __construct(
        private readonly PageFactory $pageFactory,
        private readonly State $appState,
        private readonly Config $config
    ) {
    }

    public function execute(): Page
    {
        if ($this->appState->getMode() !== State::MODE_DEVELOPER && !$this->config->allowTestPage()) {
            throw new NotFoundException(__('Page not found'));
        }

        $page = $this->pageFactory->create();
        $page->addHandle('avif_test_images');

        return $page;
    }
}
