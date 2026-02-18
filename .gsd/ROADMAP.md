# ROADMAP.md

> **Current Phase**: Phase 2: Database & Infrastructure Initialization
> **Milestone**: v0.1 — Development Readiness

## Must-Haves (from SPEC)
- [x] Working Local Environment (`.env`, `Composer`, `NPM`)
- [ ] Initialized Database with Migrations and Seeders
- [ ] Verified Modular Routing & Tenant Scoping

## Phases

### Phase 1: Environment & Dependency Setup
**Status**: ✅ Complete
**Objective**: Install all necessary dependencies and configure the environment variables for local development.
**Tasks**:
- [x] Run `composer install`
- [x] Run `npm install`
- [x] Create `.env` from `.env.example`
- [x] Generate App Key

### Phase 2: Database & Infrastructure Initialization
**Status**: ⬜ Not Started
**Objective**: Prepare the database system, run migrations, and populate initial data (Permissions, Roles, Tenants).
**Tasks**:
- [ ] Create database `tailadmin_laravel`
- [ ] Run `php artisan migrate`
- [ ] Run `php artisan db:seed` (if available) or populate Roles/Permissions
- [ ] Verify Tenant table structure

### Phase 3: Architecture Deep Dive & Module Mapping
**Status**: ⬜ Not Started
**Objective**: Detailed mapping of module interactions and multi-tenant middleware to ensure full understanding of data flow.
**Tasks**:
- [ ] Review Tenant middleware logic
- [ ] Map Retail vs Barber module differences
- [ ] Document specific Controller patterns

### Phase 4: System Verification & Proof of Life
**Status**: ⬜ Not Started
**Objective**: Run core tests and manually verify the dashboard and entry points to confirm the system is ready for work.
**Tasks**:
- [ ] Run `pest` tests
- [ ] Verify Login/Logout flow
- [ ] Execute `/verify 1` (Manual Verification)
