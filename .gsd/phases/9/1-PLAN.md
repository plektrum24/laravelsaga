---
phase: 9
plan: 1
wave: 1
---

# Plan 9.1: Attendance Integration in SalaryService

## Objective
Update the `SalaryService` to fetch real attendance data and apply financial rules (deductions for absence/lateness).

## Context
- app/Services/SalaryService.php
- app/Models/Attendance.php
- app/Models/Payroll.php

## Tasks

<task type="code">
  <name>Implement Attendance Aggregation</name>
  <action>
    Update `SalaryService::calculateMonthlySalary` to query the `attendances` table for the given employee and period.
    Count: Present, Late, Absent, Leave.
  </action>
  <verify>Check service code for query logic.</verify>
  <done>Aggregation logic implemented.</done>
</task>

<task type="code">
  <name>Implement Deduction Rules</name>
  <action>
    Add configurable or hardcoded deduction rules (e.g., -50k for unauthorized absence).
    Integrate into the final `total_amount` calculation.
  </action>
  <verify>Check calculation logic in service.</verify>
  <done>Deduction rules active.</done>
</task>

<task type="code">
  <name>Payroll Generation API</name>
  <action>
    Create a `PayrollController` or add to `EmployeeController` the ability to save the result into the `payrolls` table.
  </action>
  <verify>artisan route:list</verify>
  <done>Payroll generation endpoint created.</done>
</task>

## Success Criteria
- [ ] `SalaryService` uses real attendance counts.
- [ ] `total_amount` correctly reflects deductions.
- [ ] Payroll records can be persisted via API.
