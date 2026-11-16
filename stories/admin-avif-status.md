%title: See AVIF support in the admin
%status: complete
---

**As an** administrator,  
**I want to** see whether the server is able to create AVIF images,  
**So that** I know whether enabling the module has any effect.

## Acceptance Criteria

### when-php-gd-supports-avif

**Given** PHP GD is compiled with AVIF support  
**When** I open **Stores > Configuration > Yireo > Yireo AVIF**  
**Then** the field **AVIF support in PHP GD** says that AVIF is supported.

### when-php-gd-lacks-avif-support

**Given** PHP GD is compiled without AVIF support  
**When** I open **Stores > Configuration > Yireo > Yireo AVIF**  
**Then** the field **AVIF support in PHP GD** says that AVIF is not supported.

### when-admin-user-lacks-permission

**Given** an admin role without the ACL resource **Yireo AVIF**  
**When** a user with that role opens the configuration  
**Then** the section **Yireo AVIF** is not available.
