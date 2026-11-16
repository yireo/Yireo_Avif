# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
