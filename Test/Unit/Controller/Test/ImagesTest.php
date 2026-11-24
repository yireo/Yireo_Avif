<?php
declare(strict_types=1);

namespace Yireo\Avif\Test\Unit\Controller\Test;

use Magento\Framework\App\State;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use PHPUnit\Framework\TestCase;
use Yireo\Avif\Config\Config;
use Yireo\Avif\Controller\Test\Images;

class ImagesTest extends TestCase
{
    public function testPageIsShownInDeveloperMode(): void
    {
        $page = $this->createMock(Page::class);
        $page->expects($this->once())->method('addHandle')->with('avif_test_images');

        $this->assertSame($page, $this->getController(State::MODE_DEVELOPER, false, $page)->execute());
    }

    public function testPageIsNotFoundInProductionMode(): void
    {
        $this->expectException(NotFoundException::class);
        $this->getController(State::MODE_PRODUCTION, false, $this->createMock(Page::class))->execute();
    }

    public function testPageIsNotFoundInDefaultMode(): void
    {
        $this->expectException(NotFoundException::class);
        $this->getController(State::MODE_DEFAULT, false, $this->createMock(Page::class))->execute();
    }

    public function testPageIsShownInProductionModeWhenAllowed(): void
    {
        $page = $this->createMock(Page::class);

        $this->assertSame($page, $this->getController(State::MODE_PRODUCTION, true, $page)->execute());
    }

    private function getController(string $mode, bool $allowTestPage, Page $page): Images
    {
        $pageFactory = $this->createMock(PageFactory::class);
        $pageFactory->method('create')->willReturn($page);
        $appState = $this->createMock(State::class);
        $appState->method('getMode')->willReturn($mode);
        $config = $this->createMock(Config::class);
        $config->method('allowTestPage')->willReturn($allowTestPage);

        return new Images($pageFactory, $appState, $config);
    }
}
