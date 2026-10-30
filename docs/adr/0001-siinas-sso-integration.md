# SIINas SSO Authentication and Company Profile Synchronization

To support industrial users without duplicate account management, authentication for the `industri` role is delegated to the Kementerian Perindustrian (Kemenperin) SIINas API rather than maintaining local passwords. Upon credential verification, the system fetches the full enterprise profile from SIINas, upserts it into a dedicated `company_profiles` table, and issues our standard application JWT so downstream services interact with `industri` users identically to existing roles.

## Considered Options

- **Local password registration with manual SIINas verification**: Rejected because it requires users to remember duplicate credentials and allows out-of-sync company profile data.
- **Flattening company profile fields into `users` table**: Rejected because SIINas company data includes complex nested address lists (`alamat_kantor`, `alamat_pabrik`) and business classifications that pollute the core `users` schema.
- **Dedicated `company_profiles` table with SIINas proxy login**: Selected because it keeps `users` clean, persists nested company metadata, and enables background SIINas token reuse.
