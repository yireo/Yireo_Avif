%title: Configure AVIF encoding
%status: complete
---

**As a** shop owner,  
**I want to** configure the quality and speed of the AVIF encoder per store view,  
**So that** I can balance image quality, file size and server load.

## Acceptance Criteria

### when-quality-level-is-configured

**Given** the setting **Quality level** is set to a number between 0 and 100  
**When** an image is converted to AVIF  
**Then** the encoder uses that quality level.

### when-encoding-speed-is-configured

**Given** the setting **Encoding speed** is set to a number between 0 and 10  
**When** an image is converted to AVIF  
**Then** the encoder uses that speed.

### when-value-is-out-of-range

**Given** a quality level or speed outside of its range is stored  
**When** an image is converted to AVIF  
**Then** the nearest allowed value is used.

### when-store-view-has-own-settings

**Given** a store view has its own value for **Enabled**, **Quality level** or **Encoding speed**  
**When** a page of that store view is rendered  
**Then** the value of that store view is used.
