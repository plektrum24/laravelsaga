---
phase: 2
plan: 2
wave: 2
---

# Plan 2.2: Tenant & User Seeding

## Objective
Populate the database with initial tenant data and operational users to enable multi-tenant functionality and login access.

## Context
- .gsd/SPEC.md
- database/seeders/
- .env

## Tasks

<task type="auto">
  <name>Seed Tenancy and Roles</name>
  <files>database/seeders/*.php</files>
  <action>
    Run `php artisan db:seed --class=DatabaseSeeder` (or individual seeders if needed: `SetupRetailTenantSeeder`, `FixTenantSeeder`).
    Ensure roles and initial tenant metadata are created.
  </action>
  <verify>Query `tenants` and `roles` tables to confirm data presence.</verify>
  <done>Tenant and role data is populated.</done>
</task>

<task type="auto">
  <name>Create Operational Users</name>
  <files>database/seeders/CreateOperationalUsersSeeder.php</files>
  <action>
    Run the seeder to create administrative or operational users.
    Verify that users are associated with the correct `tenant_id`.
  </action>
  <verify>Run `php artisan model:show App\Models\User` and check counts.</verify>
  <done>Initial users are ready for login testing.</done>
</task>

## Success Criteria
- [ ] At least one tenant exists in the `tenants` table.
- [ ] Roles are populated in the `roles` table.
- [ ] Admin/Operational users exist in the `users` table.
