# Domain Glossary

Domain concepts and terms used across the IMC Reengineer system.

## Authentication & Identity

**Industri User**:
An external user representing an industrial manufacturing enterprise or company, authenticated exclusively via Kemenperin SIINas SSO.
_Avoid_: Client, vendor, public user

**SIINas**:
Sistem Informasi Industri Nasional, the national industrial information platform and identity registry managed by Kementerian Perindustrian (Kemenperin).
_Avoid_: External SSO, Gov ID

**SIINas Token (`id_siinas`)**:
The opaque long-term session token returned by SIINas upon credential verification, used to query company details and enterprise metadata.
_Avoid_: Bearer token, session id

**Company Profile**:
The official legal, organizational, and geographic details of an industrial entity registered in SIINas, synchronized and persisted into the local system.
_Avoid_: User profile, organization info

## Access Control & Authorization

**Role**:
A named classification assigned to a user that aggregates a set of permissions (e.g. `super_admin`, `admin_pusat`).
_Avoid_: Group, persona, user type

**Permission**:
A granular authorization entity defined by a resource `name` and an `action`, yielding a unique composite `code` (`{name}.{action}`, e.g. `project.create`, `user.delete`).
_Avoid_: Privilege, capability, entitlement

## Content & Organization

**Organizational Structure Group**:
A named operational division or unit within an organizational structure content item (e.g. "Dewan Pengawas", "Direksi").
_Avoid_: Department, section

**Organizational Structure Member**:
An individual position or personnel node within an organizational structure group, organized in a parent-child hierarchy via adjacency reference (`parent_id`).
_Avoid_: Employee, staff, person

**Navigation Menu (Menu, SubMenu, ChildSubMenu)**:
A 3-tier hierarchical navigation structure (`menus` -> `sub_menus` -> `child_sub_menus`) controlling routing, display order, and content association across public and administrative portals.
_Avoid_: Navigation link, routing tab, navbar item

**Home Section**:
A modular content block displayed on the portal's landing/home page, represented by a `Content` entity associated with the root 'beranda' menu.
_Avoid_: Home widget, landing component
