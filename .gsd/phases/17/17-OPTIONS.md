# Phase 17: Multi-Branch Stock Transfer

**Date:** 2026-02-21  
**Status:** PLANNING  
**Milestone:** v1.6 — Multi-Branch Operations  
**Priority:** ⭐ RECOMMENDED

---

## 📋 Context

**Current State:**
- Phase 1-16 COMPLETE ✅
- Multi-tenant system operational
- Inventory tracking per branch exists
- Stock management functional

**Problem:**
- No formal stock transfer process between branches
- Stock imbalances (overstock/stockout) across branches
- No visibility on in-transit stock
- Manual coordination required

**Solution:**
- Formal stock transfer system
- Approval workflow
- In-transit tracking
- Automated stock adjustments

---

## 🎯 Phase 17 Options

### **Option A: Multi-Branch Stock Transfer** ⭐ RECOMMENDED
**Theme:** Inventory Operations  
**Business Value:** High  
**Effort:** Medium-High (3 waves)

**Features:**
1. **Stock Transfer System**
   - Transfer requests between branches
   - Approval workflow (request → approve → ship → receive)
   - In-transit tracking
   - Receiving confirmation

2. **Transfer Documentation**
   - Transfer Orders (TO)
   - Transfer receipts
   - PDF generation
   - Discrepancy tracking

3. **Inventory Balancing**
   - Auto-adjustment on transfer
   - Movement tracking
   - Cost tracking (optional)

**Why This Option:**
- Essential for multi-location retailers
- Improves stock optimization
- Reduces stockouts and overstock
- Natural extension of Phase 15

---

### **Option B: Barcode & Label Printing**
**Theme:** Operational Efficiency  
**Business Value:** Medium-High  
**Effort:** Medium (2 waves)

**Features:**
1. **Barcode Generation**
   - Generate barcodes for products
   - Support EAN-13, UPC, Code-128
   - Bulk barcode generation

2. **Label Designer**
   - WYSIWYG label editor
   - Multiple label templates
   - Custom fields (logo, price, QR code)

3. **Print Management**
   - Direct thermal printer support
   - Batch printing
   - Print queue management

**Why This Option:**
- Solves real pain point for retailers
- Quick win (visible value)
- Professional product presentation

---

### **Option C: Purchase Orders & Procurement**
**Theme:** Supply Chain Management  
**Business Value:** High  
**Effort:** High (3 waves)

**Features:**
1. **Purchase Order System**
   - Create POs to suppliers
   - PO approval workflow
   - PO tracking (pending/partial/received)

2. **Goods Receipt**
   - Receive against PO
   - Partial receipt support
   - Quality check/rejection

3. **Supplier Management**
   - Supplier performance tracking
   - Lead time tracking
   - Price history

**Why This Option:**
- Professional procurement process
- Better supplier relationships
- Cost control & tracking
- Completes inventory management cycle

---

## 📊 Comparison Matrix

| Feature | Effort | Business Value | Technical Risk | User Impact |
|---------|--------|----------------|----------------|-------------|
| **A: Stock Transfer** | Med-High | High | Low | High |
| **B: Barcode Printing** | Medium | Med-High | Medium | High |
| **C: Purchase Orders** | High | High | Low | Medium |

---

## 🎯 Recommended Choice: **Option A - Multi-Branch Stock Transfer**

### Rationale:

1. **Builds on Phase 15:** Leverages existing inventory tracking
2. **High Business Value:** Solves real multi-branch pain point
3. **Technical Fit:** Uses existing `InventoryMovement` model
4. **Scalable:** Foundation for future supply chain features
5. **Quick ROI:** Immediate impact on stock optimization

---

## 📦 Phase 17 Structure (Option A)

### Wave 1: Stock Transfer System
**Objective:** Core transfer workflow

**Deliverables:**
- Database schema (stock_transfers, stock_transfer_items)
- Transfer request creation
- Approval workflow
- Status tracking (draft → approved → in-transit → received → cancelled)
- Auto stock adjustment on receive

**Timeline:** 3-4 days

---

### Wave 2: Transfer Documentation
**Objective:** Professional documentation

**Deliverables:**
- Transfer Order PDF generation
- Transfer receipt printing
- Email notifications
- Discrepancy handling (damaged/missing items)

**Timeline:** 2-3 days

---

### Wave 3: Analytics & Reporting
**Objective:** Visibility & optimization

**Deliverables:**
- In-transit stock report
- Transfer history
- Branch stock comparison
- Transfer analytics dashboard

**Timeline:** 2-3 days

---

## 🗄️ Database Schema (Draft)

### stock_transfers
```sql
- id: bigint
- tenant_id: bigint (FK)
- transfer_number: string (TO-YYYYMMDD-XXXX)
- from_branch_id: bigint (FK)
- to_branch_id: bigint (FK)
- requested_by: bigint (FK - user)
- approved_by: bigint (FK - user, nullable)
- shipped_by: bigint (FK - user, nullable)
- received_by: bigint (FK - user, nullable)
- status: enum (draft, pending_approval, approved, in_transit, received, cancelled)
- request_date: datetime
- approval_date: datetime
- shipped_date: datetime
- received_date: datetime
- notes: text
- total_items: integer
- created_at, updated_at: timestamp
```

### stock_transfer_items
```sql
- id: bigint
- transfer_id: bigint (FK)
- product_id: bigint (FK)
- unit_id: bigint (FK, nullable)
- qty_requested: decimal(15,4)
- qty_approved: decimal(15,4)
- qty_shipped: decimal(15,4)
- qty_received: decimal(15,4)
- qty_discrepancy: decimal(15,4)
- notes: text
- created_at, updated_at: timestamp
```

---

## 🔌 Integration Points

### Inventory Movement
- Create `InventoryMovement` record on:
  - Ship (type: `transfer_out`, from_branch)
  - Receive (type: `transfer_in`, to_branch)

### Stock Tracking
- Update `products.stock` per branch
- Track in-transit separately (optional)

### Notifications
- Email/SMS on:
  - Transfer requested (to approver)
  - Transfer approved (to requester)
  - Transfer shipped (to receiving branch)
  - Transfer received (to requesting branch)

---

## 📊 Success Metrics

| Metric | Target |
|--------|--------|
| Transfer creation time | < 2 minutes |
| Approval workflow | < 24 hours |
| Stock adjustment accuracy | 100% |
| Discrepancy tracking | 100% |
| User satisfaction | > 80% |

---

## ⚠️ Risks & Mitigation

| Risk | Impact | Mitigation |
|------|--------|------------|
| Complex workflow | Medium | Simple UI, clear status |
| Stock mismatch | High | Discrepancy tracking |
| Approval bottlenecks | Medium | Auto-approval rules (optional) |
| Performance (large transfers) | Low | Pagination, batch processing |

---

## 🚀 Next Steps

**Upon Approval:**
1. Create detailed Wave 1 plan
2. Create database migrations
3. Implement models & relationships
4. Build transfer workflow
5. Create UI components
6. Test end-to-end flow

---

## 📝 Decision Required

**Choose Phase 17 Option:**
```
A — Multi-Branch Stock Transfer (Recommended)
B — Barcode & Label Printing
C — Purchase Orders & Procurement
```

**Ready to proceed with Option A upon approval!**

---

**Phase 17 Planning Document**  
**Status:** Ready for approval  
**Recommendation:** Option A - Stock Transfer
