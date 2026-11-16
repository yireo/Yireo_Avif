# Magento 2 module for AVIF

<!-- badges.specs.start -->
![Magento version](https://img.shields.io/badge/Magento-2.4.6%20%7C%202.4.9-orange)
![PHP version](https://img.shields.io/badge/PHP-8.2%E2%80%938.5-777BB4)
![License](https://img.shields.io/badge/License-OSL--3.0-blue)
![Latest Version](https://img.shields.io/packagist/v/yireo/magento2-avif)
<!-- badges.specs.end -->

This module adds AVIF support to Magento 2, as a convertor for [Yireo NextGenImages](https://github.com/yireo/Yireo_NextGenImages). It ships with the following features:

- Local JPEG and PNG images in the frontend are converted to AVIF and added as `<source type="image/avif">` within a `<picture>` element, with the original image as fallback;
- AVIF sources are added before other next-gen formats (like WebP from [Yireo_Webp2](https://github.com/yireo/Yireo_Webp2)), so that browsers prefer AVIF;
- `srcset` candidates are converted as well;
- AVIF images are only encoded again when the original image is newer;
- PNG transparency is kept;
- Configurable quality level and encoding speed, per store view;
- The admin shows whether PHP GD supports AVIF;
- Luma swatches in product listings update the AVIF source.

## Requirements
- PHP GD compiled with AVIF support (libavif). Check this via `php -r 'var_dump(function_exists("imageavif"));'` or in the admin under **Stores > Configuration > Yireo > Yireo AVIF**. Without it, the module does nothing: the original images are shown as before.

## Known limitations
- The Luma product gallery (Fotorama) and Hyvä are not supported yet;
- AVIF encoding is slow. Images are converted when a page is first rendered, so the first request to a page with many new images is slow.

## See also:
- [Installation](INSTALL.md)
- [Testing](TESTING.md)
- [Support](SUPPORT.md)

## Requesting support
Feel free to open an **Issue** here on GitHub. However, do make sure to be thorough. See [SUPPORT.md](SUPPORT.md) for further details.

## Current status

<!-- badges.test.start -->
![Static Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_Avif/static-tests.yml?label=static-tests)
![Unit Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_Avif/unit-tests.yml?label=unit-tests)
![Integration Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_Avif/integration-tests.yml?label=integration-tests)
![Playwright](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_Avif/playwright.yml?label=playwright)
![DI Compilation](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_Avif/compile.yml?label=compile)
<!-- badges.test.end -->
