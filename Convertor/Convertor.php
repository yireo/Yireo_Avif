<?php
declare(strict_types=1);

namespace Yireo\Avif\Convertor;

use Yireo\Avif\Config\Config;
use Yireo\NextGenImages\Convertor\ConvertorInterface;
use Yireo\NextGenImages\Exception\ConvertorException;
use Yireo\NextGenImages\Image\Image;
use Yireo\NextGenImages\Image\ImageFactory;
use Yireo\NextGenImages\Image\TargetImageFactory;
use Yireo\NextGenImages\Util\File;

class Convertor implements ConvertorInterface
{
    private const SUPPORTED_MIMETYPES = ['image/jpeg', 'image/jpg', 'image/png'];

    public function __construct(
        private readonly Config $config,
        private readonly File $imageFile,
        private readonly GdEncoder $gdEncoder,
        private readonly TargetImageFactory $targetImageFactory,
        private readonly ImageFactory $imageFactory
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

        if (!in_array($image->getMimetype(), self::SUPPORTED_MIMETYPES, true)) {
            throw new ConvertorException('The mimetype "' . $image->getMimetype() . '" is not supported');
        }

        $avifImage = $this->targetImageFactory->create($image, 'avif');
        $this->convert($image->getPath(), $avifImage->getPath());

        if (!empty($image->getSrcSet())) {
            $avifImage->setSrcSet($this->convertSrcSet($image->getSrcSet()));
        }

        return $avifImage;
    }

    private function convert(string $sourceImagePath, string $targetImagePath): void
    {
        if (!$this->imageFile->fileExists($sourceImagePath)) {
            throw new ConvertorException('Source image does not exist: ' . $sourceImagePath);
        }

        if (!$this->imageFile->needsConversion($sourceImagePath, $targetImagePath)) {
            return;
        }

        if (!$this->config->allowImageCreation()) {
            throw new ConvertorException('AVIF image creation is not allowed');
        }

        $this->gdEncoder->encode(
            $sourceImagePath,
            $targetImagePath,
            $this->config->getQualityLevel(),
            $this->config->getSpeed()
        );

        if (!$this->imageFile->fileExists($targetImagePath)) {
            throw new ConvertorException('AVIF path "' . $targetImagePath . '" does not exist after conversion');
        }
    }

    private function convertSrcSet(string $srcSet): string
    {
        $avifSrcSet = [];
        foreach (explode(',', $srcSet) as $srcSetEntry) {
            $pieces = (array)preg_split('/\s+/', trim($srcSetEntry, " \f\n\r\t\v\x00"));
            $srcSetImage = $this->imageFactory->createFromUrl((string)$pieces[0]);
            $srcSetAvifImage = $this->targetImageFactory->create($srcSetImage, 'avif');
            $this->convert($srcSetImage->getPath(), $srcSetAvifImage->getPath());

            $descriptor = $pieces[1] ?? '';
            $avifSrcSet[] = trim($srcSetAvifImage->getUrl() . ' ' . $descriptor, " \f\n\r\t\v\x00");
        }

        return implode(', ', $avifSrcSet);
    }
}
