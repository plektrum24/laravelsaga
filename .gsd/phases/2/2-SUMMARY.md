# Plan 2.2 Summary

## Objective
Populate tenant and user data.

## Actions
- Configured `tenant` connection in `config/database.php` and `.env` to point to the main database (Single-DB tenancy).
- Fixed `SetupTestingTenantsSeeder.php` to include missing `code` field.
- Updated `DatabaseSeeder.php` with `updateOrInsert` logic for idempotency.
- Successfully ran `SetupTestingTenantsSeeder` and `DatabaseSeeder`.

## Verification
- Tenants created: testretail, testbarber.
- Users created for both tenants including owners and operational staff (kasir, gudang).
