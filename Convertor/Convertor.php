<?php declare(strict_types=1);

namespace Yireo\Avif\Convertor;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\File\ReadFactory as FileReadFactory;
use Magento\Framework\View\Asset\File\NotFoundException;
use Yireo\NextGenImages\Convertor\ConvertorInterface;
use Yireo\NextGenImages\Exception\ConvertorException;
use Yireo\NextGenImages\Image\File;
use Yireo\NextGenImages\Image\SourceImage;
use Yireo\NextGenImages\Image\SourceImageFactory;
use Yireo\Avif\Config\Config;
use Yireo\Avif\Image\ConvertWrapper;

class Convertor implements ConvertorInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var SourceImageFactory
     */
    private $sourceImageFactory;

    /**
     * @var File
     */
    private $imageFile;

    /**
     * @var ConvertWrapper
     */
    private $convertWrapper;

    /**
     * @var FileReadFactory
     */
    private $fileReadFactory;

    /**
     * Convertor constructor.
     * @param Config $config
     * @param SourceImageFactory $sourceImageFactory
     * @param File $imageFile
     * @param ConvertWrapper $convertWrapper
     * @param FileReadFactory $fileReadFactory
     */
    public function __construct(
        Config $config,
        SourceImageFactory $sourceImageFactory,
        File $imageFile,
        ConvertWrapper $convertWrapper,
        FileReadFactory $fileReadFactory
    ) {
        $this->config = $config;
        $this->sourceImageFactory = $sourceImageFactory;
        $this->imageFile = $imageFile;
        $this->convertWrapper = $convertWrapper;
        $this->fileReadFactory = $fileReadFactory;
    }

    /**
     * @param string $imageUrl
     * @return SourceImage
     * @throws ConvertorException
     * @deprecated Use getSourceImage() instead
     */
    public function convertByUrl(string $imageUrl): SourceImage
    {
        return $this->getSourceImage($imageUrl);
    }

    /**
     * @param string $imageUrl
     * @return SourceImage
     * @throws ConvertorException
     */
    public function getSourceImage(string $imageUrl): SourceImage
    {
        if (!$this->config->enabled()) {
            throw new ConvertorException('AVIF conversion is not enabled');
        }

        $avifUrl = $this->imageFile->convertSuffix($imageUrl, '.avif');
        $result = $this->convert($imageUrl, $avifUrl);

        if (!$result && !$this->urlExists($avifUrl)) {
            throw new ConvertorException('AVIF URL "' . $avifUrl . '" does not exist');
        }

        return $this->sourceImageFactory->create(['url' => $avifUrl, 'mimeType' => 'image/avif']);
    }

    /**
     * @param string $sourceImageUrl
     * @param string $destinationImageUrl
     * @return bool
     * @throws ConvertorException
     */
    public function convert(string $sourceImageUrl, string $destinationImageUrl): bool
    {
        $sourceImageFilename = $this->imageFile->resolve($sourceImageUrl);
        $destinationImageFilename = $this->imageFile->resolve($destinationImageUrl);

        if (!$this->needsConversion($sourceImageFilename, $destinationImageFilename)) {
            return false;
        }

        if (!$this->config->enabled()) {
            throw new ConvertorException('AVIF conversion is not enabled');
        }

        try {
            $this->convertWrapper->convert($sourceImageFilename, $destinationImageFilename);
        } catch (\Exception $e) {
            throw new ConvertorException($destinationImageFilename . ': ' . $e->getMessage());
        }

        return true;
    }

    /**
     * @param string $sourceImageFilename
     * @param string $destinationImageFilename
     * @return bool
     * @throws NotFoundException
     */
    private function needsConversion(string $sourceImageFilename, string $destinationImageFilename): bool
    {
        if (!$this->fileExists($sourceImageFilename)) {
            return false;
        }

        if (!$this->fileExists($destinationImageFilename)) {
            return true;
        }

        if ($this->imageFile->isNewerThan($destinationImageFilename, $sourceImageFilename)) {
            return false;
        }

        return false;
    }

    /**
     * @param string $url
     * @return bool
     */
    public function urlExists(string $url): bool
    {
        $filePath = $this->imageFile->resolve($url);
        if ($this->fileExists($filePath)) {
            return true;
        }

        return false;
    }

    /**
     * @param $filePath
     * @return bool
     */
    private function fileExists($filePath): bool
    {
        try {
            $fileRead = $this->fileReadFactory->create($filePath, 'file');
            return (bool)$fileRead->readAll();
        } catch (FileSystemException $fileSystemException) {
            return false;
        }
    }
}
