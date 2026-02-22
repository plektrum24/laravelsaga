# SPEC.md — Project Specification

> **Status**: `FINALIZED`
> **Last Updated**: 2026-02-21 (Phase 16 Added)

## Vision
To establish a fully functional and verified development environment for the LaravelSaga modular multi-tenant application on this local machine, ensuring all dependencies, database structures, and core features are aligned for continued development.

## Goals
1. **Developer Readiness**: Successfully install all PHP and JS dependencies and configure the environment (`.env`).
2. **Infrastructure Initialization**: Set up the local MySQL database with all necessary migrations, roles, and tenant data.
3. **Core Validation**: Verify that the primary modules (Barber/Retail) and the multi-tenant logic are functioning as expected.
4. **Knowledge Persistence**: Document the system architecture and stack for seamless future work using GSD methodology.

## Non-Goals
- New feature implementation (this phase is purely for setup and analysis).
- Production deployment or staging configuration.
- Comprehensive UI/UX redesign.

## Users
- **Primary**: Developers (specifically on this laptop) needing a clean, verified environment.

## Constraints
- Must stay compatible with Laravel 12 and PHP 8.2+.
- Single-database tenancy must be maintained as per the current schema.
- Must follow the GSD development methodology.

## Success Criteria
- [ ] `composer install` and `npm install` complete without errors.
- [ ] All migrations run and permissions/seeders are populated.
- [ ] The application dashboard loads and authenticates correctly.
- [ ] `/map` and `/audit-milestone` return positive results for system health.

---

# PHASE 16 SPEC: Customer Loyalty Program

> **Status**: `DRAFT` → `FINALIZED` (upon approval)
> **Phase**: 16
> **Milestone**: v1.5 — Customer Engagement & Retention

## Vision (Phase 16)
Implement a comprehensive customer loyalty program to increase customer retention, drive repeat purchases, and provide retail tenants with a competitive advantage through points earning, redemption, and membership tiers.

## Goals (Phase 16)

### Wave 1: Core Points System
1. **Points Ledger**: Complete tracking of all points earned/redeemed
2. **Earn on Purchase**: Automatic points calculation on transaction completion
3. **Redeem at Checkout**: Points redemption as payment method
4. **Admin Configuration**: Configurable earn rates and redemption rules
5. **Customer Balance**: View points balance and history

### Wave 2: Membership Tiers
1. **Tier System**: Bronze/Silver/Gold membership levels
2. **Auto-Promotion**: Automatic tier upgrades based on spend/visits
3. **Tier Benefits**: Discounts, bonus points, special perks
4. **Progress Tracking**: Visual progress to next tier

### Wave 3: Rewards & Campaigns
1. **Reward Catalog**: Configurable rewards for points redemption
2. **Birthday Rewards**: Automatic rewards on customer birthday
3. **Campaign System**: Promotional point multipliers
4. **Customer Dashboard**: Self-service points and rewards view

## Requirements (Phase 16)

### Functional Requirements

**FR-1: Points Earning**
- Customers earn points on completed transactions
- Points calculated as: `floor(total_amount / earn_rate)`
- Configurable earn rate per tenant (default: 1 point per Rp 10,000)
- Points credited only after transaction completion
- Points have expiry date (configurable, default: 12 months)

**FR-2: Points Redemption**
- Customers can redeem points at checkout
- Configurable redemption rate (default: 1 point = Rp 100)
- Maximum redemption percentage per transaction (default: 50%)
- Minimum points required for redemption (default: 100 points)
- Redemption creates payment line item

**FR-3: Points Ledger**
- Immutable record of all points transactions
- Types: earn, redeem, adjust, expire, refund
- Reference to source (transaction, adjustment, campaign)
- Running balance calculation

**FR-4: Membership Tiers**
- Multiple tiers with increasing benefits
- Qualification based on rolling 12-month spend or visit count
- Automatic tier assessment on transaction completion
- Tier benefits apply automatically at checkout
- Tier history tracking

**FR-5: Admin Configuration**
- Loyalty program enable/disable
- Earn rate configuration
- Redemption rate configuration
- Min/max redemption settings
- Points expiry settings
- Tier configuration

### Non-Functional Requirements

**NFR-1: Performance**
- Points calculation: <100ms
- Balance query: <50ms
- Must support 1000+ concurrent checkouts

**NFR-2: Data Integrity**
- Points ledger must be immutable
- All points changes must be transactional
- Daily reconciliation report

**NFR-3: Multi-Tenancy**
- Complete isolation per tenant
- Tenant-specific configuration
- No cross-tenant data access

## Constraints (Phase 16)
- Must integrate with existing POS system
- Must work with existing Customer model
- Backward compatible with existing transactions
- No breaking changes to existing APIs

## Success Criteria (Phase 16)

### Wave 1 Success Criteria
- [ ] Transaction completion creates points record
- [ ] Points balance calculates correctly
- [ ] Redemption reduces points balance
- [ ] Admin can configure loyalty settings
- [ ] Customer can view points balance
- [ ] API endpoints return accurate data
- [ ] All changes are tenant-scoped

### Wave 2 Success Criteria
- [ ] Tiers configureable per tenant
- [ ] Customers auto-promote based on rules
- [ ] Tier benefits apply at checkout
- [ ] Progress tracking displays correctly
- [ ] Tier history recorded

### Wave 3 Success Criteria
- [ ] Reward catalog CRUD functional
- [ ] Customers can redeem rewards
- [ ] Birthday rewards auto-issue
- [ ] Customer dashboard shows all data
- [ ] Campaign performance tracked

## Technical Design

### Database Schema

**customer_points** (Points Ledger)
```sql
- id: bigint
- customer_id: bigint (FK)
- tenant_id: bigint (FK)
- points: decimal(15,2)
- type: enum (earn, redeem, adjust, expire, refund)
- reference_type: string (transaction, adjustment, campaign, reward)
- reference_id: bigint
- expiry_date: datetime
- balance_after: decimal(15,2)
- notes: text
- created_at: timestamp
```

**loyalty_settings** (Tenant Configuration)
```sql
- id: bigint
- tenant_id: bigint (FK, unique)
- earn_rate: decimal(15,2) (amount per point)
- earn_currency: string (default: IDR)
- point_value: decimal(15,4) (value per point)
- min_redemption_points: integer
- max_redemption_percent: decimal(5,2)
- points_expiry_months: integer
- enabled: boolean
- created_at, updated_at: timestamp
```

**membership_tiers** (Tier Configuration)
```sql
- id: bigint
- tenant_id: bigint (FK)
- name: string (Bronze, Silver, Gold, Platinum)
- min_spend: decimal(15,2)
- min_visits: integer
- benefits: json (discount_percent, points_multiplier, etc)
- badge_color: string
- priority: integer
- active: boolean
- created_at, updated_at: timestamp
```

**customer_tiers** (Customer Tier Assignment)
```sql
- id: bigint
- customer_id: bigint (FK)
- tier_id: bigint (FK)
- qualified_at: timestamp
- valid_until: timestamp
- previous_tier_id: bigint (nullable)
- created_at, updated_at: timestamp
```

**reward_catalog** (Available Rewards)
```sql
- id: bigint
- tenant_id: bigint (FK)
- name: string
- description: text
- points_cost: integer
- stock: integer (null for infinite)
- image_url: string
- terms_conditions: text
- active_from: datetime
- active_to: datetime
- status: enum (draft, active, inactive)
- created_at, updated_at: timestamp
```

**customer_rewards** (Redemption History)
```sql
- id: bigint
- customer_id: bigint (FK)
- reward_id: bigint (FK)
- points_redeemed: integer
- status: enum (pending, fulfilled, expired, cancelled)
- fulfilled_at: datetime
- expiry_date: datetime
- notes: text
- created_at, updated_at: timestamp
```

### API Endpoints

**Wave 1:**
```
GET    /api/loyalty/settings           - Get loyalty config
POST   /api/loyalty/settings           - Update loyalty config
GET    /api/customers/{id}/points      - Get customer points balance
GET    /api/customers/{id}/points/history - Get points ledger
POST   /api/loyalty/calculate          - Calculate points for transaction
POST   /api/loyalty/redeem             - Redeem points
```

**Wave 2:**
```
GET    /api/tiers                      - Get available tiers
GET    /api/customers/{id}/tier        - Get customer tier
POST   /api/tiers/calculate-progress   - Calculate tier progress
```

**Wave 3:**
```
GET    /api/rewards                    - Get reward catalog
POST   /api/rewards                    - Create reward
PUT    /api/rewards/{id}               - Update reward
DELETE /api/rewards/{id}               - Delete reward
POST   /api/rewards/redeem             - Redeem reward
GET    /api/customers/{id}/rewards     - Get customer rewards
```

### Integration Points

**TransactionController@store**
- After transaction completion:
  - Calculate points earned
  - Create customer_points record (type: earn)
  - Apply tier benefits if applicable

**TransactionController@cancel**
- On transaction cancellation:
  - Reverse points earned (type: refund)
  - Restore redeemed points (type: refund)

**POS Frontend**
- Display points earned preview
- Show redeemable points
- Add points redemption input
- Show tier badge

---

## Risks & Mitigation

| Risk | Impact | Mitigation |
|------|--------|------------|
| Points calculation errors | High | Extensive testing, reconciliation reports |
| Performance degradation | Medium | Indexing, caching, query optimization |
| Fraud/abuse | Medium | Audit trail, redemption limits |
| Low adoption | Medium | Marketing templates, easy onboarding |

---

**SPEC Status**: Ready for implementation  
**Next Step**: Create Wave 1 detailed plan
