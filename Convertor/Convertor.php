<?php declare(strict_types=1);

namespace Yireo\Avif\Convertor;

use GdImage;
use Throwable;
use Yireo\NextGenImages\Convertor\ConvertorInterface;
use Yireo\NextGenImages\Exception\ConvertorException;
use Yireo\NextGenImages\Image\Image;
use Yireo\Avif\Config\Config;
use Yireo\NextGenImages\Image\TargetImageFactory;

class Convertor implements ConvertorInterface
{
    public function __construct(
        private Config $config,
        private TargetImageFactory $targetImageFactory
    ) {
    }

    public function convertImage(Image $image): Image
    {
        if (!$this->config->enabled()) {
            throw new ConvertorException('AVIF conversion is not enabled');
        }

        $gdImage = $image->getGdImage();
        if (false === $gdImage instanceof GdImage) {
            throw new ConvertorException('Unable to convert image to GD image');
        }

        if (false === function_exists('imageavif')) {
            throw new ConvertorException('PHP does not support AVIF');
        }

        $avifImage = $this->targetImageFactory->create($image, 'avif');

        try {
            imageavif($gdImage, $avifImage->getPath());
        } catch (Throwable $exception) {
            throw new ConvertorException($exception->getMessage());
        }

        return $avifImage;
    }
}
