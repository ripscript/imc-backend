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
