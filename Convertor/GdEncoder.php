<?php
declare(strict_types=1);

namespace Yireo\Avif\Convertor;

use GdImage;
use Throwable;
use Yireo\NextGenImages\Exception\ConvertorException;

class GdEncoder
{
    public function isSupported(): bool
    {
        return function_exists('imageavif');
    }

    public function encode(string $sourcePath, string $targetPath, int $quality, int $speed): void
    {
        if (false === $this->isSupported()) {
            throw new ConvertorException('PHP GD does not support AVIF');
        }

        $gdImage = $this->createGdImage($sourcePath);

        try {
            $result = imageavif($gdImage, $targetPath, $quality, $speed);
        } catch (Throwable $exception) {
            throw new ConvertorException($targetPath . ': ' . $exception->getMessage());
        }

        if (false === $result) {
            throw new ConvertorException('Unable to write AVIF image to "' . $targetPath . '"');
        }
    }

    private function createGdImage(string $sourcePath): GdImage
    {
        try {
            $imageSize = getimagesize($sourcePath);
            $imageType = is_array($imageSize) ? $imageSize[2] : null;
            $gdImage = match ($imageType) {
                IMAGETYPE_JPEG => imagecreatefromjpeg($sourcePath),
                IMAGETYPE_PNG => imagecreatefrompng($sourcePath),
                default => false,
            };
        } catch (Throwable) {
            $gdImage = false;
        }

        if (false === $gdImage instanceof GdImage) {
            throw new ConvertorException('Unable to read image "' . $sourcePath . '" with GD');
        }

        if ($imageType === IMAGETYPE_PNG) {
            imagepalettetotruecolor($gdImage);
            imagealphablending($gdImage, false);
            imagesavealpha($gdImage, true);
        }

        return $gdImage;
    }
}
