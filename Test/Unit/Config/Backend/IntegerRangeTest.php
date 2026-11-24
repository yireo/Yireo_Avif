<?php
declare(strict_types=1);

namespace Yireo\Avif\Test\Unit\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yireo\Avif\Config\Backend\IntegerRange;

class IntegerRangeTest extends TestCase
{
    #[DataProvider('getValidValues')]
    public function testValidValueIsSaved(mixed $value, string $expected): void
    {
        $model = $this->getModel(0, 10);
        $model->setValue($value);
        $model->beforeSave();

        $this->assertSame($expected, $model->getValue());
    }

    /**
     * @return array<array{mixed, string}>
     */
    public static function getValidValues(): array
    {
        return [
            ['0', '0'],
            ['10', '10'],
            [' 6 ', '6'],
            [6, '6'],
        ];
    }

    #[DataProvider('getInvalidValues')]
    public function testInvalidValueIsRejected(mixed $value): void
    {
        $model = $this->getModel(0, 10);
        $model->setValue($value);

        $this->expectException(ValidatorException::class);
        $model->beforeSave();
    }

    /**
     * @return array<array{mixed}>
     */
    public static function getInvalidValues(): array
    {
        return [
            ['11'],
            ['-1'],
            [''],
            ['abc'],
            ['5.5'],
            ['1e1'],
            [null],
            ["5\n6"],
        ];
    }

    private function getModel(int $min, int $max): IntegerRange
    {
        $context = $this->createMock(Context::class);
        $context->method('getEventDispatcher')->willReturn($this->createMock(ManagerInterface::class));

        return new IntegerRange(
            $context,
            $this->createMock(Registry::class),
            $this->createMock(ScopeConfigInterface::class),
            $this->createMock(TypeListInterface::class),
            $min,
            $max
        );
    }
}
