%title: Update AVIF sources when switching swatches
%status: pending
---

**As a** visitor,  
**I want to** see the image of the selected swatch in a product listing,  
**So that** I see the product variant I picked.

## Acceptance Criteria

### when-swatch-with-avif-image-is-selected

**Given** a product listing with a configurable product using Luma swatches  
**When** I select a swatch whose image has an AVIF version  
**Then** the AVIF source of the product image points to the AVIF version of the swatch image.

### when-swatch-without-avif-image-is-selected

**Given** a product listing with a configurable product using Luma swatches  
**When** I select a swatch whose image has no AVIF version  
**Then** the AVIF source is removed, so that the swatch image itself is shown.
