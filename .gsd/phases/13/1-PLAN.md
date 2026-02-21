---
phase: 13
plan: 1
wave: 1
---

# Plan 13.1: POS Integration & Unit Handling

## Objective
Finalize the POS system by replacing mock logic with real API integrations and robust unit/price handling.

## User Review Required
> [!IMPORTANT]
> This change will strictly enforce inventory levels. If a product is out of stock, the POS will prevent sales unless "allow negative stock" is enabled for that product.

## Proposed Changes

### Backend
1. **Category API**: Ensure `CategoryController@index` is properly scoped to the tenant.
2. **Transaction Persistence**: Update `TransactionController@store` to handle:
   - Dynamic `unit_id` from the cart.
   - Price verification against the specific unit.
   - Inventory reservation/deduction per unit factor.

### Frontend (`pos/index.blade.php`)
1. **Alpine.js Logic**:
   - Replace mock `categories` with fetch from `/api/categories`.
   - Update `processCheckout` to send a `POST` request to `/api/transactions`.
   - Add a unit selector to the cart or product card if multiple units exist.
   - Implement a post-checkout receipt viewing experience (reuse PDF logic if possible).

## Tasks

<task type="code">
  <name>POS Category Integration</name>
  <action>
    Fetch real categories and update the filter.
  </action>
  <done>Category filter is dynamic.</done>
</task>

<task type="code">
  <name>Real Checkout Implementation</name>
  <action>
    Connect "Pay Now" to the real backend endpoint.
  </action>
  <done>Transactions saved to DB.</done>
</task>

<task type="code">
  <name>Multi-Unit Cart Support</name>
  <action>
    Allow users to switch between PCS, Pack, Karton, etc., in the POS.
  </action>
  <done>Correct prices and stock factors applied.</done>
</task>

## Success Criteria
- [ ] Products show real prices and categories.
- [ ] Clicking "Pay Now" creates a `Transaction` record in the database.
- [ ] Stock is deducted correctly based on the sold unit's conversion factor.
