# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.9.9] - 25 November 2026
### Fixed
- Prevent path traversal and srcset injection: only JPEG/PNG files inside pub/media, pub/static or var/tmp are converted and srcset descriptors and URLs are validated
- Reject images above 40 megapixels before decoding them, to prevent memory exhaustion
- Write AVIF images atomically via a temporary file and lock conversion per target image
- Keep absolute server paths out of exception messages
- Hide the AVIF test page /avif/test/images outside of developer mode, unless the hidden setting yireo_avif/test/enabled is set
- Validate AVIF quality level and encoding speed when saving the configuration
- Fail with a clear error when the AVIF target folder is missing or not writable
- Turn errors for missing source images into regular conversion errors instead of uncaught exceptions

## [0.9.8] - 16 November 2026
### Fixed
- Require NextGenImages 0.5.16 and declare used modules only
- Update AVIF source with the AVIF image of the selected swatch
- Reuse up-to-date AVIF images and verify conversion
- Add missing ACL resource `Yireo_Avif::config`

### Added
- Add quality, encoding speed and AVIF support status

## [0.9.7] - 22 October 2025
### Fixed
- Add proper support AVIF via GD even though this causes conversion issues most of the time

## [0.9.6] - 8 April 2025
### Fixed
- Compat psr/log with Magento 2.4.7
