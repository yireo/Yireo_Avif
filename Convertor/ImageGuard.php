<?php
declare(strict_types=1);

namespace Yireo\Avif\Convertor;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Yireo\NextGenImages\Config\Config as NextGenImagesConfig;

class ImageGuard
{
    /**
     * @param string[] $directories
     * @param string[] $pubFolders
     */
    public function __construct(
        private readonly DirectoryList $directoryList,
        private readonly NextGenImagesConfig $nextGenImagesConfig,
        private readonly array $directories = [DirectoryList::MEDIA, DirectoryList::STATIC_VIEW, DirectoryList::TMP],
        private readonly array $pubFolders = ['media', 'static']
    ) {
    }

    public function isSupportedSource(string $path): bool
    {
        if (!preg_match('/\.(jpe?g|png)$/iD', $path)) {
            return false;
        }

        return $this->isWithin($path, $this->getAllowedDirectories());
    }

    public function isAllowedTarget(string $path): bool
    {
        if (!preg_match('/\.avif$/iD', $path)) {
            return false;
        }

        $directories = $this->getAllowedDirectories();
        $directories[] = $this->nextGenImagesConfig->getCacheDirectoryPath();

        return $this->isWithin($path, $directories);
    }

    public function isValidUrl(string $url): bool
    {
        return $url !== '' && !preg_match('/[\s"\'<>`,]/', $url);
    }

    public function isValidSrcSetDescriptor(string $descriptor): bool
    {
        return $descriptor === '' || (bool)preg_match('/^\d+(\.\d+)?[wx]$/D', $descriptor);
    }

    /**
     * @param string[] $directories
     */
    private function isWithin(string $path, array $directories): bool
    {
        if (str_contains($path, "\0") || !str_starts_with($path, '/')) {
            return false;
        }

        $segments = explode('/', $path);
        if (in_array('..', $segments, true) || in_array('.', $segments, true)) {
            return false;
        }

        foreach ($directories as $directory) {
            $directory = rtrim($directory, '/') . '/';
            if ($directory !== '/' && str_starts_with($path, $directory)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string[]
     */
    private function getAllowedDirectories(): array
    {
        $paths = [];
        foreach ($this->pubFolders as $pubFolder) {
            $paths[] = $this->directoryList->getRoot() . '/pub/' . $pubFolder;
        }

        foreach ($this->directories as $directory) {
            try {
                $paths[] = $this->directoryList->getPath($directory);
            } catch (FileSystemException) {
                continue;
            }
        }

        return $paths;
    }
}
