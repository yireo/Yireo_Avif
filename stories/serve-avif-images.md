%title: Serve AVIF images in the frontend
%status: complete
---

**As a** shop owner,  
**I want to** serve AVIF versions of my JPEG and PNG images,  
**So that** visitors download smaller images and pages load faster.

## Acceptance Criteria

### when-page-contains-jpeg-or-png-image

**Given** the module is enabled and PHP GD supports AVIF  
**When** a visitor opens a page containing a local JPEG or PNG image  
**Then** the image is wrapped in a `picture` element with a `source` of type `image/avif`  
**And** the original image stays as fallback for browsers without AVIF support.

### when-webp-is-also-enabled

**Given** both this module and Yireo_Webp2 are enabled  
**When** a page with images is rendered  
**Then** the AVIF source is listed before the WebP source, so that browsers prefer AVIF.

### when-image-has-srcset

**Given** an image has a `srcset` with multiple candidates  
**When** the image is converted  
**Then** every candidate is converted to AVIF  
**And** the AVIF `srcset` keeps the original descriptors.

### when-avif-image-is-up-to-date

**Given** an AVIF image already exists and is newer than its original  
**When** the page is rendered again  
**Then** the AVIF image is reused and not encoded again.

### when-original-image-changes

**Given** an AVIF image exists but is older than its original  
**When** the page is rendered again  
**Then** the AVIF image is encoded again.

### when-module-is-disabled

**Given** the setting **Enabled** is set to **No**  
**When** a visitor opens a page with images  
**Then** no AVIF sources are added and no AVIF files are requested.

### when-image-creation-is-not-allowed

**Given** the Yireo NextGenImages setting **Convert images** is set to **No**  
**When** an image has no AVIF version yet  
**Then** no AVIF image is created and no AVIF source is added.

### when-image-type-is-not-supported

**Given** a page contains a GIF, WebP or SVG image  
**When** the page is rendered  
**Then** no AVIF source is added for that image.

### when-php-gd-lacks-avif-support

**Given** PHP GD is compiled without AVIF support  
**When** a visitor opens a page with images  
**Then** the original images are shown without AVIF sources  
**And** no broken AVIF URLs are rendered.
