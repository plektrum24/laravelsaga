---
phase: 10
plan: 1
wave: 1
---

# Plan 10.1: Salary Configuration & Payroll Preview UI

## Objective
Build the UI components to manage employee salary attributes and provide a preview of monthly payroll before generation.

## Context
- `app/Modules/Retail/Views/employees/` (or similar path)
- `resources/js/components/` (Alpine.js integration)
- API endpoints from Phase 8 & 9

## Tasks

<task type="code">
  <name>Salary Configuration Form</name>
  <action>
    Add a "Salary" tab or section to the Employee Edit view.
    Include fields for base salary, allowances, and bank information.
  </action>
  <verify>Check UI for new fields.</verify>
  <done>Form updated.</done>
</task>

<task type="code">
  <name>Payroll Preview Modal</name>
  <action>
    Implement a button/modal in the Employee List or Profile to "Preview Monthly Payroll".
    Fetch data from `api/employees/{id}/payroll-preview`.
  </action>
  <verify>Modal displays correct calc from API.</verify>
  <done>Preview UI working.</done>
</task>

<task type="code">
  <name>Payroll Record Persistence UI</name>
  <action>
    Add "Generate & Save Payroll" action in the preview modal.
    Call `POST api/payrolls`.
  </action>
  <verify>Record created in database after action.</verify>
  <done>Persistence UI implemented.</done>
</task>

## Success Criteria
- [ ] Users can edit salary data via the web UI.
- [ ] Users can preview monthly payroll with attendance-based deductions.
- [ ] Users can save/confirm payroll records.
