<?php
declare(strict_types=1);

namespace Yireo\Avif\Image;

use Yireo\Avif\Config\Config;

/**
 * Class ConvertWrapper to wrap third party wrapper for purpose of preference overrides and testing
 */
class ConvertWrapper
{
    /**
     * @var Config
     */
    private $config;

    /**
     * ConvertWrapper constructor.
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @param string $sourceImageFilename
     * @param string $destinationImageFilename
     * @throws ConversionFailedException
     */
    public function convert(string $sourceImageFilename, string $destinationImageFilename): void
    {
        throw new \Exception('Not implemented yet');
    }

    /**
     * @return mixed[]
     */
    public function getOptions(): array
    {
        return [
        ];
    }
}
