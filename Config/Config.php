<?php
declare(strict_types=1);

namespace Yireo\Avif\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Yireo\NextGenImages\Config\Config as NextGenImagesConfig;

class Config implements ArgumentInterface
{
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly StoreManagerInterface $storeManager,
        private readonly NextGenImagesConfig $nextGenImagesConfig
    ) {
    }

    public function enabled(): bool
    {
        return (bool)$this->getValue('yireo_avif/settings/enabled');
    }

    public function allowImageCreation(): bool
    {
        return $this->nextGenImagesConfig->allowImageCreation();
    }

    public function getQualityLevel(): int
    {
        return $this->getIntegerInRange('yireo_avif/settings/quality_level', 0, 100);
    }

    public function getSpeed(): int
    {
        return $this->getIntegerInRange('yireo_avif/settings/speed', 0, 10);
    }

    private function getIntegerInRange(string $path, int $min, int $max): int
    {
        return max($min, min($max, (int)$this->getValue($path)));
    }

    private function getValue(string $path): mixed
    {
        try {
            return $this->scopeConfig->getValue(
                $path,
                ScopeInterface::SCOPE_STORE,
                $this->storeManager->getStore()->getId()
            );
        } catch (NoSuchEntityException) {
            return null;
        }
    }
}
