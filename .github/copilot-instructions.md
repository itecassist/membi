# Copilot Instructions for Membix

## Decision Log (read this first)
- **Previous system**: `membi-gitlab` (Node.js/Express + Prisma + Keycloak + Preact/Next.js). Abandoned due to: Windows→WSL→Docker inotify hot-reload failures, two competing V1/V2 API systems never reconciled, 70% fix ratio vs 30% coding.
- **Decision**: Clean rewrite on Laravel (DDD) + Next.js, using membi-gitlab as a *reference implementation only*.
- **Reference codebase**: `d:\Projects\membi-gitlab` — use its schema.zmodel (67KB), TenantCheckoutManager, E2E tests as living documentation/acceptance criteria.

## Big Picture
Membix is a SaaS membership management platform for clubs and societies.
- **Spec**: `docs/spec.md` — primary source of truth for features and behaviour.
- **Tech stack**: Laravel 11 (PHP 8.3), PostgreSQL, Next.js (App Router), Tailwind CSS.
- **Auth**: Laravel Sanctum (token-based API auth + 2FA). No external IAM server.
- **Payments**: GoCardless (Direct Debit) + WorldPay (card payments). Both in scope.
- **Deployment**: Docker (prod). Laravel dev server + Vite for local dev — NO WSL/Docker requirement for development.

## Monorepo Structure
```
membix/
  backend/          ← Laravel 11 API (DDD structure)
  frontend/         ← Next.js App Router (to be created)
  docs/
    spec.md         ← Master product spec
```

## Backend Architecture (DDD)
The backend uses Domain-Driven Design. Code lives in `backend/app/Domain/` not `backend/app/`.

```
backend/app/
  Domain/
    Organisation/
      Models/         ← Eloquent models scoped to domain
      Services/       ← Business logic (no HTTP concerns)
      Repositories/   ← DB query encapsulation
      Actions/        ← Single-responsibility operations
      Events/         ← Domain events
      Listeners/
    Member/           ← Members, Groups, Membership forms
    Subscription/     ← Subscription types, options, instances, pricing
    Order/            ← Orders, order items, checkout flow
    Payment/          ← GoCardless, WorldPay drivers
    Event/            ← Org events, bookings, sessions (Phase 2)
    Communication/    ← Emails, templates
    Content/          ← Help articles, FAQs
  Http/
    Controllers/Api/  ← Thin controllers, delegate to Services
    Requests/         ← Form request validation
    Resources/        ← API response transformers
  Providers/
```

## Key Domain Model Decisions
These were explicitly agreed — do not second-guess them:

1. **`users` table only holds identity** — email, password, social auth IDs, 2FA. No name/DOB/phone duplication. Members are org-scoped profiles.
2. **`members` table is org-scoped** — links `users` to `organisations` with org-specific fields (member_number, joined_at, classification).
3. **Groups are first-class** — `groups` table with `group_members` pivot. Types: `family` and `corporate`. Group has: name, type, email_contact_id, group admins, member inheritance of addresses.
4. **`member_subscriptions` table** — the instance of "member X holds subscription type Y from [start] to [end] with payment method Z, renewal_status W". Separate from `subscription_price_options`.
5. **Events** are in scope for Phase 1 but after core member/subscription/order flow.
6. **Soft deletes** on all primary domain models.
7. **All IDs are UUIDs** (use `$table->uuid('id')->primary()`).

## Subscription Domain
Based on spec.md and membi-gitlab's SubscriptionType/SubscriptionTypeOption/SubscriptionInstance pattern:
- `subscriptions` = the *type* (Annual Membership, Monthly Paddle etc.)
- `subscription_price_options` = pricing variants per type (Adult/Junior/Family, price tiers)
- `member_subscriptions` = *instance* (member X's active subscription, with dates, payment method, renewal status)
- Renewal types: `auto_renew`, `manual`, `not_renewable`
- Period types: `day`, `week`, `month`, `year`, `lifetime`, `none`, `instalments`
- Pricing types: `flat`, `family`, `corporate`, `tiered`, `custom_variable`

## Payment Methods in Scope
- **GoCardless** (Direct Debit) — recurring, mandate-based
- **WorldPay** (Card) — one-off and recurring
- **Offline** (admin-managed): Bank Transfer, Cheque, Cash, Standing Order

## Database Conventions
- All PKs: `$table->uuid('id')->primary()`
- All FKs: `$table->foreignUuid('xxx_id')->constrained()->cascadeOnDelete()` (or `nullOnDelete()` where appropriate)
- Soft deletes: `$table->softDeletes()` on all primary entities
- All boolean columns default explicitly (no ambiguous null)
- Migrations named: `YYYY_MM_DD_HHMMSS_verb_noun_table.php`
- Existing migration issues to fix (new migrations, not edits): users.mobile_phone nullable, users.gender nullable, users.last_login_at nullable, subscription_auto_renewals.payment_method should be FK not boolean

## API Conventions
- All routes under `/api/v1/`
- Resource controllers (Laravel standard)
- Responses via API Resources — never raw eloquent output
- Validation in Form Requests only
- Business logic in Domain Services — never in controllers
- Use `$request->user()` (Sanctum) not custom auth abstractions
- All endpoints return JSON with consistent envelope: `{ data: ..., meta?: ..., message?: ... }`

## Testing
- Feature tests in `backend/tests/Feature/`
- Unit tests in `backend/tests/Unit/`
- Use Pest PHP (already installed — `pestphp/pest`)
- Reference tests: `membi-gitlab/apps/api/__tests__/` for acceptance criteria patterns

## Current Build Phase
**Phase 1 — Foundation** (current focus):
- [x] DDD folder structure
- [x] Schema fixes (new corrective migrations)
- [x] Groups migration + model
- [x] `member_subscriptions` migration + model
- [x] Laravel Sanctum auth (register, login, logout) ← 2FA will not be implemented
- [x] Organisation + Member CRUD
- [x] Subscription type + price option CRUD

**Phase 2 — Core transaction flow**:
- [x] Checkout flow
- [x] Orders + order items
- [x] GoCardless integration
- [x] WorldPay integration
- [x] Renewal jobs

**Phase 3 — Admin & Member portals**:
- [ ] Next.js frontend (admin + member)

**Phase 4 — Events + Beta**:
- [ ] Events, bookings, sessions
- [ ] Data migration from WebCollect

## Useful References in membi-gitlab
When implementing complex logic, check these first:
- Schema: `apps/api/schema/schema.zmodel` — full data model
- Checkout: `apps/api/lib/managers/checkout/v2/TenantCheckoutManager.ts` — state machine
- Subscription pricing: `apps/api/lib/subscriptions/pricing/SubscriptionOptionPricingCalculator.ts`
- Group logic: `apps/api/lib/managers/groups/TenantGroupManager.ts`
- Tests as acceptance criteria: `apps/api/__tests__/`
