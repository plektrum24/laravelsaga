---
phase: 11
plan: 1
wave: 1
---

# Plan 11.1: Batch Payroll Processing

## Objective
Implement backend and frontend support for generating monthly payroll records for all active employees simultaneously.

## Proposed Changes

### Backend
1. **SalaryService**: Add `calculateBulk(string $period)` to handle multiple employees.
2. **PayrollController**: 
   - Add `bulkPreview(Request $request)` to see the total impact.
   - Add `bulkStore(Request $request)` to persist all records.

### Frontend
1. **Payroll Dashboard**:
   - Add "Generate Bulk Payroll" button.
   - Implement a summary modal showing:
     - Total employees to be processed.
     - Total expected payout.
     - Warning for employees missing salary config.
   - Progress bar or notification upon completion.

## Tasks

<task type="code">
  <name>Batch Logic in SalaryService</name>
  <action>
    Implement a method to fetch all active employees and run the calculation loop.
  </action>
  <verify>Check if method returns array of results for all active employees.</verify>
  <done>Logic implemented.</done>
</task>

<task type="code">
  <name>Bulk API Endpoints</name>
  <action>
    Register `POST api/payrolls/bulk` and `GET api/payrolls/bulk-preview`.
  </action>
  <done>Endpoints registered and handled by Controller.</done>
</task>

<task type="code">
  <name>Bulk Generation UI</name>
  <action>
    Update `payroll/index.blade.php` to include the bulk action trigger and summary view.
  </action>
  <done>UI implemented.</done>
</task>

## Success Criteria
- [ ] Admin can preview total payout for the month.
- [ ] Admin can generate all payroll records with one confirmation.
- [ ] Records correctly linked to each employee with their respective deductions.
