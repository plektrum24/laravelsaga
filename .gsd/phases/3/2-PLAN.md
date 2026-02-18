---
phase: 3
plan: 2
wave: 2
---

# Plan 3.2: Module Mapping & Capability Analysis

## Objective
Map the differences between Retail and Barber modules and identify the core capabilities supported by each.

## Context
- app/Modules/Retail/Config/menu.php
- app/Modules/Barber/Config/menu.php
- app/Http/Controllers/Api/MenuController.php

## Tasks

<task type="auto">
  <name>Map Retail Module Capabilities</name>
  <files>app/Modules/Retail/**/*</files>
  <action>
    Identify core Retail features: POS, Inventory, Partners, Debt Management.
    Document the Controller/Model relationship for these features.
  </action>
  <verify>List of Retail capabilities documented.</verify>
  <done>Retail module is mapped.</done>
</task>

<task type="auto">
  <name>Analyze Barber Module (Gap Analysis)</name>
  <files>app/Modules/Barber/**/*</files>
  <action>
    Verify that Barber is primarily a configuration shell.
    Identify planned features (Booking, Queue) based on `menu.php`.
    Note any shared components (like Settings).
  </action>
  <verify>Barber module status documented.</verify>
  <done>Barber module gap analysis complete.</done>
</task>

<task type="auto">
  <name>Update ARCHITECTURE.md</name>
  <files>.gsd/ARCHITECTURE.md</files>
  <action>
    Refine the Architecture document with the findings from Phase 3.
    Add a section for "Module Capabilities" and "Tenant Isolation".
  </action>
  <verify>Review updated ARCHITECTURE.md.</verify>
  <done>Architecture documentation is up-to-date.</done>
</task>

## Success Criteria
- [ ] Detailed module map in ARCHITECTURE.md.
- [ ] Documented "Single-DB/Multi-Tenant" hybrid strategy.
