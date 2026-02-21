---
phase: 4
plan: 1
wave: 1
---

# Plan 4.1: Manual Verification & Proof of Life

## Objective
Verify the core application flows (Login, Dashboard, Sidebar, Employee Module) manually and document the "Proof of Life" since automated tests are blocked by PHP version mismatch.

## Context
- app/Http/Middleware/TenantMiddleware.php
- app/Http/Controllers/Api/MenuController.php
- resources/views/pages/employees/index.blade.php

## Tasks

<task type="manual">
  <name>Verify User Access & Tenant Context</name>
  <action>
    Log in as 'retail@sagatoko.com' (password: 12345678).
    Verify that the Dashboard loads correctly.
    Check that the 'X-Tenant-ID' or user context correctly identifies 'Toko Retail Jaya'.
  </action>
  <verify>Visual check of Dashboard and User Profile.</verify>
  <done>Access verified.</done>
</task>

<task type="manual">
  <name>Verify Sidebar & Module Loading</name>
  <action>
    Verify 'Team Karyawan' sidebar menu is visible.
    Navigate to 'Data Karyawan', 'Kelola Team', and 'Via Management'.
    Ensure all pages render without asset errors (Vite builds).
  </action>
  <verify>Navigation works smoothly across all 3 sub-menus.</verify>
  <done>Sidebar and module loading verified.</done>
</task>

<task type="manual">
  <name>Final Hygiene Check</name>
  <action>
    Check browser console for any JS errors (Alpine.js or Axios).
    Check Laravel logs for any backend errors during navigation.
  </action>
  <verify>Clean console and logs.</verify>
  <done>System stability verified.</done>
</task>

## Success Criteria
- [ ] Successful navigation to all new Employee module pages.
- [ ] No major JS/Backend errors during basic smoke test.
- [ ] Verification report finalized.
