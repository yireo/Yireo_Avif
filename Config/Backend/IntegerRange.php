<?php
declare(strict_types=1);

namespace Yireo\Avif\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class IntegerRange extends Value
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        private readonly int $min = 0,
        private readonly int $max = 100,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    public function beforeSave()
    {
        $value = trim((string)$this->getValue(), " \f\n\r\t\v\x00");
        if (!preg_match('/^\d+$/D', $value) || (int)$value < $this->min || (int)$value > $this->max) {
            throw new ValidatorException(
                __('The value of "%1" must be a number between %2 and %3', $this->getFieldLabel(), $this->min, $this->max)
            );
        }

        $this->setValue($value);

        return parent::beforeSave();
    }

    private function getFieldLabel(): string
    {
        $fieldConfig = $this->getData('field_config');
        if (is_array($fieldConfig) && isset($fieldConfig['label'])) {
            return (string)$fieldConfig['label'];
        }

        return (string)$this->getPath();
    }
}
