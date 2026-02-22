# Phase 16: Customer Loyalty Program

**Status:** `DRAFT` → Pending approval  
**Milestone:** v1.5 — Customer Engagement & Retention  
**Effort:** High (3-4 waves)  
**Priority:** ⭐ RECOMMENDED

---

## 📋 Objective

Implement a comprehensive customer loyalty program to increase customer retention, drive repeat purchases, and provide retail tenants with a competitive advantage.

---

## 🎯 Goals

1. **Points System:** Customers earn points on purchases, redeemable for rewards
2. **Membership Tiers:** Bronze/Silver/Gold with increasing benefits
3. **Rewards Engine:** Configurable rewards and promotional campaigns
4. **Customer Visibility:** Dashboard for points balance and rewards

---

## 📦 Deliverables

### Wave 1: Core Points System (Foundation)
**Objective:** Basic earn & burn loyalty points

**Tasks:**
1. **Database Schema**
   - `customer_points` table (ledger)
   - `loyalty_settings` table (earn rates, redemption rules)
   - `customer_rewards` table (redemption history)
   - `reward_catalog` table (available rewards)

2. **Backend Logic**
   - Points calculation on transaction completion
   - Points redemption at checkout
   - Points expiry handling
   - Points ledger tracking

3. **API Endpoints**
   - `GET /api/loyalty/settings` - Get loyalty configuration
   - `POST /api/loyalty/calculate` - Calculate points for transaction
   - `GET /api/customers/{id}/points` - Customer points balance
   - `GET /api/customers/{id}/points/history` - Points ledger
   - `POST /api/loyalty/redeem` - Redeem points at checkout

4. **Admin Configuration**
   - Loyalty settings page
   - Points earn rate (e.g., 1 point per Rp 10,000)
   - Points value (e.g., 1 point = Rp 100)
   - Minimum redemption threshold

**Verification:**
- Transaction creates points record
- Points balance updates correctly
- Redemption reduces balance
- API returns accurate data

---

### Wave 2: Membership Tiers
**Objective:** Tiered membership with progressive benefits

**Tasks:**
1. **Database Extensions**
   - `membership_tiers` table (benefits configuration)
   - Add `tier_id` to `customers` table
   - `tier_history` table (track changes)

2. **Tier Logic**
   - Auto-promotion based on spend/visits
   - Tier benefits application (discounts, bonuses)
   - Tier validity & renewal reminders
   - Tier downgrade rules (optional)

3. **Benefits Engine**
   - Tier-specific discount rates
   - Bonus points multipliers
   - Early access to sales
   - Free shipping thresholds

4. **API Extensions**
   - `GET /api/customers/{id}/tier` - Current tier & benefits
   - `GET /api/tiers` - Available tiers
   - `POST /api/tiers/calculate-progress` - Progress to next tier

5. **UI Components**
   - Tier badge on customer profile
   - Progress bar to next tier
   - Tier benefits display
   - Tier history timeline

**Verification:**
- Customers auto-promote based on rules
- Tier benefits apply at checkout
- Progress tracking accurate
- UI displays tier information

---

### Wave 3: Rewards & Campaigns
**Objective:** Engaging rewards and promotional campaigns

**Tasks:**
1. **Reward Catalog**
   - Create/manage rewards
   - Points cost configuration
   - Reward availability (stock/infinite)
   - Reward categories

2. **Campaign System**
   - Birthday rewards (auto-issue)
   - Visit milestone rewards
   - Referral bonuses
   - Promotional point multipliers

3. **Customer Dashboard**
   - Points balance & expiry
   - Available rewards
   - Redemption history
   - Tier status & progress

4. **Redemption Flow**
   - Browse reward catalog
   - Redeem points for rewards
   - Reward fulfillment tracking
   - Reward expiry handling

5. **Admin Management**
   - Reward catalog management
   - Campaign creation
   - Redemption fulfillment
   - Campaign performance analytics

**Verification:**
- Rewards catalog displays correctly
- Redemption flow works end-to-end
- Campaigns trigger automatically
- Customer dashboard shows all data

---

### Wave 4: Analytics & Optimization (Optional)
**Objective:** Insights and optimization tools

**Tasks:**
1. **Loyalty Analytics**
   - Points earned vs redeemed
   - Tier distribution
   - Reward popularity
   - Campaign ROI

2. **Customer Segmentation**
   - Top customers by points
   - At-risk customers (low activity)
   - High-value segments
   - Churn prediction

3. **Marketing Integration**
   - Email notifications (tier change, points expiry)
   - SMS notifications (optional)
   - Push notifications (if mobile app)

4. **Optimization Tools**
   - A/B testing for campaigns
   - Points value optimization
   - Tier threshold analysis

**Verification:**
- Analytics dashboard functional
- Segments calculate correctly
- Notifications send on triggers

---

## 🗄️ Database Schema (Draft)

### customer_points
```sql
id | customer_id | points | type (earn/redeem/adjust/expiry) | 
reference_type (transaction/campaign/adjustment) | reference_id | 
expiry_date | notes | created_at
```

### loyalty_settings
```sql
id | tenant_id | earn_rate (1 point per X) | 
earn_currency (IDR) | point_value (1 point = Y IDR) | 
min_redemption | max_redemption_percent | 
points_expiry_months | enabled | created_at
```

### membership_tiers
```sql
id | tenant_id | name (Bronze/Silver/Gold) | 
min_spend | min_visits | benefits (JSON) | 
badge_color | priority | created_at
```

### reward_catalog
```sql
id | tenant_id | name | description | points_cost | 
stock | image_url | terms_conditions | 
active_from | active_to | status | created_at
```

### customer_rewards
```sql
id | customer_id | reward_id | points_redeemed | 
status (pending/fulfilled/expired/cancelled) | 
fulfilled_at | expiry_date | notes | created_at
```

---

## 🔌 Integration Points

### POS Integration
- Points calculation during checkout
- Points redemption option
- Tier discount application
- Receipt shows points earned/balance

### Customer Module
- Customer profile shows tier & points
- Points history tab
- Reward redemption page

### Transaction System
- Transaction completion triggers points award
- Transaction cancellation reverses points
- Points redemption creates payment line item

### Reporting
- Loyalty analytics dashboard
- Points liability report
- Reward redemption reports

---

## ⚠️ Assumptions & Dependencies

**Assumptions:**
- Customer module already exists ✅
- POS system can accept additional payment types ✅
- Multi-tenant architecture supports new tables ✅
- Users have email/SMS for notifications (optional)

**Dependencies:**
- Phase 13 (POS System) ✅ Complete
- Phase 14 (Analytics) ✅ Complete
- Phase 15 (Inventory) ✅ Complete

**Technical Constraints:**
- Must support multi-tenant isolation
- Points ledger must be immutable (audit trail)
- Must handle concurrent redemptions safely
- Performance: <100ms points calculation

---

## 📊 Success Metrics

| Metric | Target |
|--------|--------|
| Points calculation accuracy | 100% |
| Redemption flow completion | <30 seconds |
| Customer dashboard load time | <2 seconds |
| Points ledger accuracy | 100% (reconciled daily) |
| Tier promotion accuracy | 100% |

---

## 🎯 Risks & Mitigation

| Risk | Impact | Mitigation |
|------|--------|------------|
| Points fraud/abuse | High | Audit trail, redemption limits, anomaly detection |
| Performance degradation | Medium | Indexing, caching, batch processing |
| Complex tier logic | Medium | Clear rules, extensive testing |
| Low customer adoption | Medium | Marketing templates, easy onboarding |

---

## 📝 Next Steps

**Pending your decision:**

1. **Approve Option A** (or choose alternative)
2. **Finalize SPEC.md** with requirements
3. **Create detailed Wave 1 plan**
4. **Begin implementation**

---

**Ready to proceed upon approval!**

```
▶ NEXT

/approve phase-16-option-a — Start Loyalty Program implementation
/discuss phase-16 — Discuss alternative options
/plan phase-16-option-b — See Stock Transfer plan
/plan phase-16-option-c — See Barcode Printing plan
```
