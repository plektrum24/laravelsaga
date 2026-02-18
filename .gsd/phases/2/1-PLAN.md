---
phase: 2
plan: 1
wave: 1
---

# Plan 2.1: Database Migration & Permissions

## Objective
Establish the database schema and initialize the foundational permission tables required for RBAC.

## Context
- .gsd/SPEC.md
- .gsd/ARCHITECTURE.md
- database/migrations/
- .env

## Tasks

<task type="auto">
  <name>Run Database Migrations</name>
  <files>database/migrations/*.php</files>
  <action>
    Ensure MySQL (XAMPP) is running.
    Run `php artisan migrate` using the XAMPP PHP executable.
    Verify that all 11 migration files are executed successfully.
  </action>
  <verify>Check for table existence (users, tenants, permissions, roles, etc.) via `php artisan db:show`.</verify>
  <done>Database schema is fully initialized.</done>
</task>

<task type="auto">
  <name>Verify Permission Tables</name>
  <files>database/migrations/2026_01_25_034449_create_permission_tables.php</files>
  <action>
    Confirm the `spatie/laravel-permission` table structures are present.
    Verify that the `tenant_id` columns are added as per the tenant migration sequence.
  </action>
  <verify>Run `php artisan model:show Spatie\Permission\Models\Role`.</verify>
  <done>Permission system infrastructure is ready.</done>
</task>

## Success Criteria
- [ ] 11 migrations completed.
- [ ] Core tables (users, tenants, roles, permissions) exist.
