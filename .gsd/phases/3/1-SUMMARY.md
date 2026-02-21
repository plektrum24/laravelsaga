# Plan 3.1 Summary: Tenancy Logic Deep Dive

## Findings

### 1. Tenant Identification
- **Mechanism**: Handled by `App\Http\Middleware\TenantMiddleware`.
- **Precedence**:
    1. `X-Tenant-ID` header (allows switching for Super Admins/Owners).
    2. `tenant_id` field on the authenticated `User` model.
- **Switching**: The middleware dynamically updates the `tenant` connection configuration (`database.connections.tenant.database`) and reconnects the DB.

### 2. Data Isolation
- **Pattern**: Hybrid "Single-DB/Multi-Database" ready.
- **Current Status**: Both `default` and `tenant` connections point to `tailadmin_laravel` in `.env`.
- **Scoping**:
    - A `MultiTenantable` trait exists in `App\Traits\MultiTenantable` which applies a Global Scope for `tenant_id`.
    - **CRITICAL GAP**: This trait is currently only applied to `TransactionItem`. All other models (Product, User, Branch, etc.) are **NOT** automatically scoped, increasing the risk of cross-tenant data leakage.

### 3. Recommendations
- Apply `MultiTenantable` trait to all models resides in the `tenant` schema.
- Ensure all repository/service queries use the `tenant` connection for scoped models.
