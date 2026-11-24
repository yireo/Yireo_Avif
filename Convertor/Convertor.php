<?php
declare(strict_types=1);

namespace Yireo\Avif\Convertor;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Lock\LockManagerInterface;
use Magento\Framework\View\Asset\File\NotFoundException;
use Yireo\Avif\Config\Config;
use Yireo\NextGenImages\Convertor\ConvertorInterface;
use Yireo\NextGenImages\Exception\ConvertorException;
use Yireo\NextGenImages\Image\Image;
use Yireo\NextGenImages\Image\ImageFactory;
use Yireo\NextGenImages\Image\TargetImageFactory;
use Yireo\NextGenImages\Util\File;

class Convertor implements ConvertorInterface
{
    private const LOCK_PREFIX = 'yireo_avif_';

    public function __construct(
        private readonly Config $config,
        private readonly File $imageFile,
        private readonly GdEncoder $gdEncoder,
        private readonly TargetImageFactory $targetImageFactory,
        private readonly ImageFactory $imageFactory,
        private readonly ImageGuard $imageGuard,
        private readonly LockManagerInterface $lockManager
    ) {
    }

    public function getFormat(): string
    {
        return 'avif';
    }

    public function convertImage(Image $image): Image
    {
        if (!$this->config->enabled()) {
            throw new ConvertorException('AVIF conversion is not enabled');
        }

        $avifImage = $this->createTargetImage($image);
        $this->convert($image->getPath(), $avifImage->getPath());

        if (!empty($image->getSrcSet())) {
            $avifImage->setSrcSet($this->convertSrcSet($image->getSrcSet()));
        }

        return $avifImage;
    }

    private function createTargetImage(Image $image): Image
    {
        if (!$this->imageGuard->isSupportedSource($image->getPath())) {
            throw new ConvertorException('The image "' . basename($image->getPath()) . '" is not supported');
        }

        try {
            $avifImage = $this->targetImageFactory->create($image, 'avif');
        } catch (FileSystemException|NotFoundException $exception) {
            throw new ConvertorException($exception->getMessage());
        }

        if (!$this->imageGuard->isValidUrl($avifImage->getUrl())) {
            throw new ConvertorException('Invalid AVIF URL');
        }

        return $avifImage;
    }

    private function convert(string $sourceImagePath, string $targetImagePath): void
    {
        if (!$this->imageGuard->isAllowedTarget($targetImagePath)) {
            throw new ConvertorException('AVIF path "' . basename($targetImagePath) . '" is not allowed');
        }

        if (!$this->imageFile->fileExists($sourceImagePath)) {
            throw new ConvertorException('Source image does not exist: ' . basename($sourceImagePath));
        }

        if (!$this->imageFile->needsConversion($sourceImagePath, $targetImagePath)) {
            return;
        }

        if (!$this->config->allowImageCreation()) {
            throw new ConvertorException('AVIF image creation is not allowed');
        }

        $lockName = self::LOCK_PREFIX . md5($targetImagePath);
        $locked = $this->acquireLock($lockName, $targetImagePath);

        try {
            $this->gdEncoder->encode(
                $sourceImagePath,
                $targetImagePath,
                $this->config->getQualityLevel(),
                $this->config->getSpeed()
            );
        } finally {
            if ($locked) {
                $this->lockManager->unlock($lockName);
            }
        }

        if (!$this->imageFile->fileExists($targetImagePath)) {
            throw new ConvertorException(
                'AVIF path "' . basename($targetImagePath) . '" does not exist after conversion'
            );
        }
    }

    private function acquireLock(string $lockName, string $targetImagePath): bool
    {
        try {
            if ($this->lockManager->lock($lockName, 0)) {
                return true;
            }
        } catch (AlreadyExistsException) {
            return false;
        }

        throw new ConvertorException('AVIF image "' . basename($targetImagePath) . '" is being converted');
    }

    private function convertSrcSet(string $srcSet): string
    {
        $avifSrcSet = [];
        foreach (explode(',', $srcSet) as $srcSetEntry) {
            $pieces = (array)preg_split('/\s+/', trim($srcSetEntry, " \f\n\r\t\v\x00"));
            $url = (string)$pieces[0];
            $descriptor = (string)($pieces[1] ?? '');
            if (count($pieces) > 2 || !$this->imageGuard->isValidSrcSetDescriptor($descriptor)) {
                throw new ConvertorException('Invalid srcset descriptor');
            }

            try {
                $srcSetImage = $this->imageFactory->createFromUrl($url);
            } catch (FileSystemException|NotFoundException $exception) {
                throw new ConvertorException($exception->getMessage());
            }

            $srcSetAvifImage = $this->createTargetImage($srcSetImage);
            $this->convert($srcSetImage->getPath(), $srcSetAvifImage->getPath());

            $avifSrcSet[] = trim($srcSetAvifImage->getUrl() . ' ' . $descriptor, " \f\n\r\t\v\x00");
        }

        return implode(', ', $avifSrcSet);
    }
}
