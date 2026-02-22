# SPEC.md — Phase 17: Multi-Branch Stock Transfer

> **Status**: `FINALIZED`  
> **Phase**: 17  
> **Milestone**: v1.6 — Multi-Branch Operations  
> **Priority**: HIGH

---

## Vision

Enable seamless stock transfers between branches with formal approval workflow, in-transit tracking, and automated stock adjustments to optimize inventory distribution across multi-location retail operations.

---

## Goals

### Wave 1: Stock Transfer System
1. **Transfer Creation**: Create stock transfer requests between branches
2. **Approval Workflow**: Multi-level approval (request → approve → ship → receive)
3. **Status Tracking**: Real-time visibility on transfer status
4. **Auto Stock Adjustment**: Automatic stock updates on transfer completion

### Wave 2: Transfer Documentation
1. **Transfer Order PDF**: Professional transfer documentation
2. **Receipt Printing**: Receiving confirmation documents
3. **Email Notifications**: Automated notifications on status changes
4. **Discrepancy Handling**: Track damaged/missing items

### Wave 3: Analytics & Reporting
1. **In-Transit Report**: Visibility on pending transfers
2. **Transfer History**: Complete audit trail
3. **Branch Stock Comparison**: Cross-branch inventory analysis
4. **Transfer Analytics**: Performance metrics

---

## Requirements

### Functional Requirements

**FR-1: Transfer Creation**
- Create transfer from source branch to destination branch
- Add multiple products with quantities
- Validate stock availability at source branch
- Generate transfer number automatically (TO-YYYYMMDD-XXXX)
- Save as draft or submit for approval

**FR-2: Approval Workflow**
- Pending approval status after submission
- Approver can approve, reject, or request changes
- Approval requires manager-level permission
- Auto-approval option for low-value transfers (configurable)
- Track approver and approval timestamp

**FR-3: Transfer Fulfillment**
- Pick and pack process at source branch
- Mark as shipped with actual quantities
- Update status to "in-transit"
- Create inventory movement (transfer_out)

**FR-4: Transfer Receiving**
- Receive at destination branch
- Record actual quantities received
- Handle discrepancies (damaged, missing)
- Update status to "received"
- Create inventory movement (transfer_in)
- Adjust stock at both branches

**FR-5: Transfer Cancellation**
- Cancel transfers in draft or pending status
- Cannot cancel approved/shipped transfers
- Require cancellation reason
- Restore stock if already deducted

**FR-6: Stock Management**
- Deduct stock from source on ship
- Add stock at destination on receive
- Track in-transit stock (optional)
- Create audit trail in inventory_movements

### Non-Functional Requirements

**NFR-1: Performance**
- Transfer creation: < 2 seconds
- Stock validation: < 500ms
- Support 100+ transfers per day

**NFR-2: Data Integrity**
- Atomic stock updates
- Prevent negative stock
- Complete audit trail
- Tenant isolation

**NFR-3: Usability**
- Intuitive UI
- Clear status indicators
- Mobile-friendly (tablet for warehouse)

---

## Technical Design

### Database Schema

**stock_transfers**
```sql
- id: bigint (PK)
- tenant_id: bigint (FK → tenants)
- transfer_number: string (unique index)
- from_branch_id: bigint (FK → branches)
- to_branch_id: bigint (FK → branches)
- requested_by: bigint (FK → users)
- approved_by: bigint (FK → users, nullable)
- shipped_by: bigint (FK → users, nullable)
- received_by: bigint (FK → users, nullable)
- status: enum ('draft', 'pending_approval', 'approved', 'in_transit', 'received', 'cancelled')
- request_date: datetime
- approval_date: datetime (nullable)
- shipped_date: datetime (nullable)
- received_date: datetime (nullable)
- notes: text (nullable)
- total_items: integer (default 0)
- created_at: timestamp
- updated_at: timestamp

Indexes:
- tenant_id
- status
- from_branch_id, to_branch_id
- created_at
```

**stock_transfer_items**
```sql
- id: bigint (PK)
- transfer_id: bigint (FK → stock_transfers)
- product_id: bigint (FK → products)
- unit_id: bigint (FK → units, nullable)
- qty_requested: decimal(15,4)
- qty_approved: decimal(15,4) (nullable)
- qty_shipped: decimal(15,4)
- qty_received: decimal(15,4)
- qty_discrepancy: decimal(15,4) (default 0)
- notes: text (nullable)
- created_at: timestamp
- updated_at: timestamp

Indexes:
- transfer_id
- product_id
```

### API Endpoints

**Wave 1:**
```
GET    /api/stock-transfers              - List transfers
POST   /api/stock-transfers              - Create transfer
GET    /api/stock-transfers/{id}         - Get transfer details
PUT    /api/stock-transfers/{id}         - Update transfer
DELETE /api/stock-transfers/{id}         - Cancel transfer
POST   /api/stock-transfers/{id}/submit  - Submit for approval
POST   /api/stock-transfers/{id}/approve - Approve transfer
POST   /api/stock-transfers/{id}/reject  - Reject transfer
POST   /api/stock-transfers/{id}/ship    - Ship transfer
POST   /api/stock-transfers/{id}/receive - Receive transfer
GET    /api/stock-transfers/{id}/print   - Print transfer order
```

**Wave 2:**
```
POST   /api/stock-transfers/{id}/discrepancy - Report discrepancy
GET    /api/stock-transfers/reports/in-transit - In-transit report
GET    /api/stock-transfers/reports/history    - Transfer history
```

### State Machine

```
draft → pending_approval → approved → in_transit → received
  |            |               |           |
  |            ↓               ↓           ↓
  └────────→ cancelled ←────────┴───────────┘
```

**Transitions:**
- `draft → pending_approval`: Submit for approval
- `draft → cancelled`: Cancel draft
- `pending_approval → approved`: Approve
- `pending_approval → rejected`: Reject
- `pending_approval → cancelled`: Cancel pending
- `approved → in_transit`: Ship
- `approved → cancelled`: Cancel approved (restore stock)
- `in_transit → received`: Receive
- `in_transit → cancelled`: Cancel in-transit (exceptional)

---

## Integration Points

### Inventory Movement
```php
// On ship
InventoryMovement::create([
    'type' => 'transfer_out',
    'reference_type' => 'stock_transfer',
    'reference_id' => $transfer->id,
    'branch_id' => $transfer->from_branch_id,
    // ...
]);

// On receive
InventoryMovement::create([
    'type' => 'transfer_in',
    'reference_type' => 'stock_transfer',
    'reference_id' => $transfer->id,
    'branch_id' => $transfer->to_branch_id,
    // ...
]);
```

### Stock Tracking
```php
// On ship - deduct from source
Product::where('branch_id', $from_branch_id)
    ->decrement('stock', $qty_shipped);

// On receive - add at destination
Product::where('branch_id', $to_branch_id)
    ->increment('stock', $qty_received);
```

---

## Success Criteria

### Wave 1
- [ ] Transfer CRUD functional
- [ ] Approval workflow working
- [ ] Status transitions correct
- [ ] Stock auto-adjusted on ship/receive
- [ ] Inventory movements created
- [ ] UI for transfer management

### Wave 2
- [ ] PDF generation working
- [ ] Email notifications sent
- [ ] Discrepancy handling functional
- [ ] Receipt printing working

### Wave 3
- [ ] In-transit report accurate
- [ ] Transfer history complete
- [ ] Branch comparison dashboard
- [ ] Analytics metrics displayed

---

## Risks & Mitigation

| Risk | Impact | Mitigation |
|------|--------|------------|
| Stock mismatch | High | Discrepancy tracking, audit trail |
| Workflow complexity | Medium | Simple UI, clear status |
| Performance (bulk transfers) | Medium | Batch processing, pagination |
| Multi-tenant data leak | High | Tenant scoping on all queries |

---

**SPEC Status**: Ready for implementation  
**Next Step**: Create Wave 1 detailed plan
