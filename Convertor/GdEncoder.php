<?php
declare(strict_types=1);

namespace Yireo\Avif\Convertor;

use GdImage;
use Throwable;
use Yireo\NextGenImages\Exception\ConvertorException;

class GdEncoder
{
    public const MAX_PIXELS = 40_000_000;

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
        $tmpPath = $targetPath . '.tmp-' . bin2hex(random_bytes(4));

        try {
            $result = imageavif($gdImage, $tmpPath, $quality, $speed);
            if (false === $result || false === rename($tmpPath, $targetPath)) {
                throw new ConvertorException('Unable to write AVIF image "' . basename($targetPath) . '"');
            }
        } catch (ConvertorException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new ConvertorException(
                'Unable to write AVIF image "' . basename($targetPath) . '"',
                0,
                $exception
            );
        } finally {
            if (file_exists($tmpPath)) {
                unlink($tmpPath);
            }
        }
    }

    private function createGdImage(string $sourcePath): GdImage
    {
        try {
            $imageSize = getimagesize($sourcePath);
        } catch (Throwable) {
            $imageSize = false;
        }

        $imageType = is_array($imageSize) ? $imageSize[2] : null;
        if (is_array($imageSize) && $imageSize[0] * $imageSize[1] > self::MAX_PIXELS) {
            throw new ConvertorException('Image "' . basename($sourcePath) . '" is too large to convert');
        }

        try {
            $gdImage = match ($imageType) {
                IMAGETYPE_JPEG => imagecreatefromjpeg($sourcePath),
                IMAGETYPE_PNG => imagecreatefrompng($sourcePath),
                default => false,
            };
        } catch (Throwable) {
            $gdImage = false;
        }

        if (false === $gdImage instanceof GdImage) {
            throw new ConvertorException('Unable to read image "' . basename($sourcePath) . '" with GD');
        }

        if ($imageType === IMAGETYPE_PNG) {
            imagepalettetotruecolor($gdImage);
            imagealphablending($gdImage, false);
            imagesavealpha($gdImage, true);
        }

        return $gdImage;
    }
}
