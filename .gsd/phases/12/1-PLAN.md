---
phase: 12
plan: 1
wave: 1
---

# Plan 12.1: Payroll Formalization & Reporting

## Objective
Provide administrative tools to export payroll data into formal formats (PDF, Excel) and visualize monthly labor costs.

## Proposed Changes

### Backend
1. **Reporting Service**: Create `PayrollReportService` to handle data formatting.
2. **Export Controller**: Create `PayrollExportController` using:
   - `Barryvdh\DomPDF\Facade\Pdf` for individual slips.
   - `Maatwebsite\Excel\Facades\Excel` for monthly summaries.
3. **Endpoints**:
   - `GET /api/payrolls/{id}/pdf`: Single slip download.
   - `GET /api/payrolls/export/excel`: Monthly batch export.

### Frontend
1. **Payroll Table**:
   - Add "Download PDF" icon button to each row.
   - Add "Export Excel" button to the header (next to bulk generate).
2. **Reporting Component**:
   - Add a summary card showing "Total Labor Cost" trend (simple bar chart or stat card).

## Tasks

<task type="code">
  <name>PDF Salary Slip Template</name>
  <action>
    Create a professional Blade view for the salary slip.
  </action>
  <done>Blade template designed.</done>
</task>

<task type="code">
  <name>Export API Endpoints</name>
  <action>
    Register `/api/payrolls/{id}/pdf` and `/api/payrolls/export/excel`.
  </action>
  <done>Endpoints registered.</done>
</task>

<task type="code">
  <name>Frontend Export Actions</name>
  <action>
    Update UI to trigger downloads.
  </action>
  <done>UI buttons added and linked.</done>
</task>

## Success Criteria
- [ ] Admin can download a clean PDF slip for any employee.
- [ ] Admin can export a full month's payroll to Excel.
- [ ] Data in exports matches the database records accurately.
