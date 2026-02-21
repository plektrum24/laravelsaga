---
phase: 8
plan: 1
wave: 1
---

# Plan 8.1: Salary Attributes & Basic Calculation

## Objective
Extend the `employees` table with financial attributes and implement the core logic for salary calculation.

## Context
- app/Models/Employee.php
- database/migrations/ (to be created)
- app/Services/SalaryService.php (to be created)

## Tasks

<task type="code">
  <name>Database Migration: Salary Attributes</name>
  <action>
    Create a migration to add `base_salary`, `transport_allowance`, `meal_allowance`, and `position_allowance` to `employees`.
    Run the migration.
  </action>
  <verify>php artisan migrate</verify>
  <done>Database updated.</done>
</task>

<task type="code">
  <name>Model Update: Employee</name>
  <action>
    Update `Employee.php` to include salary attributes in `$fillable`.
    Add a `getNetSalaryAttribute` accessor.
  </action>
  <verify>Check Employee model content.</verify>
  <done>Model updated.</done>
</task>

<task type="code">
  <name>Service Implementation: SalaryService</name>
  <action>
    Create `app/Services/SalaryService.php` to handle complex payroll calculations.
  </action>
  <verify>Check file existence and structure.</verify>
  <done>Service implemented.</done>
</task>

## Success Criteria
- [ ] `employees` table has new salary columns.
- [ ] `Employee` model correctly calculates net salary from components.
- [ ] All code linted and verified.
