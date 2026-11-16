<?php
declare(strict_types=1);

namespace Yireo\Avif\Test\Integration\Stub;

use Yireo\Avif\Convertor\GdEncoder;
use Yireo\NextGenImages\Exception\ConvertorException;

class FakeGdEncoder extends GdEncoder
{
    /**
     * @var array<int, array{string, string, int, int}>
     */
    public array $calls = [];

    public function __construct(
        private readonly bool $supported = true,
        private readonly bool $writeTarget = true
    ) {
    }

    public function isSupported(): bool
    {
        return $this->supported;
    }

    public function encode(string $sourcePath, string $targetPath, int $quality, int $speed): void
    {
        if (false === $this->supported) {
            throw new ConvertorException('PHP GD does not support AVIF');
        }

        $this->calls[] = [$sourcePath, $targetPath, $quality, $speed];
        if ($this->writeTarget) {
            copy($sourcePath, $targetPath);
        }
    }
}
