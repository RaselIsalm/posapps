# ONE POS mobile API v1

The Flutter app uses the Laravel website as its single data store. Configure the app with the website origin, for example:

```sh
flutter build apk --dart-define=API_BASE_URL=https://pos.onetech.bd
```

The app appends `/api/v1`. Set `API_BASE_URL` to the website origin without a trailing slash, and do not include `/api` or `/api/v1`. The API is JSON except for the authenticated PDF download routes. There is no public user registration; owners and administrators create users and grant roles in the website.

Live deployment check (2026-11-06): `https://pos.onetech.bd/api/v1/auth/login` and `/api/v1/auth/me` return HTTP 404, while `/api/admin/test` returns HTTP 200. This shows the live host is reachable but does not currently serve this API v1 route set. Deploy the current Laravel API routes and bootstrap configuration, clear stale Laravel caches, and verify `php artisan route:list --path=api/v1` on the server includes these routes. Then send an empty JSON body to `POST /api/v1/auth/login` and confirm it returns a JSON validation response (HTTP 422, not 404) before building a live-connected APK. Do not include `/api/v1` in `API_BASE_URL`; doing so would create duplicate path segments.

## Authentication and response format

`POST /api/v1/auth/login` accepts `email` (email address or username) and `password`. It returns `data.access.token` and `data.user`. Tokens are Sanctum bearer tokens that expire after 30 days. Send `Authorization: Bearer <token>` and `Accept: application/json` on protected calls. `POST /auth/logout` revokes the current token. `GET /auth/me` refreshes the user and branch assignments. Inactive accounts are rejected and their current token is revoked.

Successful JSON responses use `{ "message": "...", "data": { ... } }`. Laravel validation errors return HTTP 422, unauthenticated/expired sessions return 401, permission or branch violations return 403, missing records return 404, and blocked deletions return 409. Paginated lists contain the named array plus `total`, `current_page`, `per_page`, and `last_page`.

## Implemented endpoints

| Method | Path | Permission / behavior |
|---|---|---|
| POST | `/auth/login` | Public; inactive users and invalid credentials are rejected |
| GET | `/auth/me` | Current user, permissions, assigned branches |
| POST | `/auth/logout` | Revokes current Sanctum token |
| POST | `/profile/update` | Current user profile |
| PUT | `/change/password` | Current password required; minimum 8 characters |
| GET | `/general-settings` | Website currency settings and the configured default POS tax rate |
| GET | `/dashboard`, `/reports` | Website calculations, date period via `type` |
| GET | `/products`, `/product/search`, `/product/details/{id}`, `/add/product/info` | Product catalog, POS prices and branch stock |
| POST | `/product/store`; DELETE `/product/delete/{id}` | Product creation and guarded deletion; variation create is rejected until the mobile form supports the website variant payload |
| GET | `/categories`, `/categories/parent-category`; POST `/categories/store`; PUT or POST `/categories/update/{id}`; DELETE `/categories/delete/{id}` | Category management; POST supports the current Flutter multipart form |
| GET | `/brands`; POST `/brands/store`; PUT or POST `/brands/update/{id}`; DELETE `/brands/delete/{id}` | Brand management; POST supports the current Flutter multipart form |
| GET | `/warehouses`, `/pos/branches`; POST `/warehouses/store`; PUT `/warehouses/update/{id}`; DELETE `/warehouses/delete/{id}` | Warehouses in Flutter map to website branches |
| GET | `/customer/search`, `/customer/groups`; POST `/customer/store` | POS customer search and creation |
| POST | `/apply/promo/code` | Server coupon validation; `code` and app `price` are accepted |
| GET | `/payment-gateway-list?branch_id={id}` | Returns the active account assigned to the selected branch |
| POST | `/pos/store` | `product_ids[]`, `qty[]`, `customer_id`, `coupon_id`, `branch_id`, `payment_method`, `type`, optional `sale_id`; product prices, discounts, configured tax, stock and payment account are resolved server-side |
| GET | `/drafts`; DELETE `/draft/delete/{id}` | Drafts are private to the signed-in user and retain customer, coupon and branch |
| GET | `/invoice/{saleId}` | Authenticated branch-authorized PDF invoice |
| GET | `/purchase`, `/sales`, `/expense` | Search, pagination, date range, permitted branch scope |
| GET | `/purchase/pdf`, `/sale/pdf` | Returns an authenticated PDF URL; date and branch filters are forwarded |
| GET | `/purchase/export`, `/sale/export` | Authenticated report PDF |
| GET | `/accounts`, `/admin/accounts`, `/balance` | Account list and branch scoped balance |
| POST | `/balance/transfer` | Branch account deposit; server validates account and branch access |
| GET | `/sale/payment/success/{saleId}` | Branch-authorized sale payment status |

For list endpoints, use `search`, `page`, and `per_page`. Financial lists accept `from_date`/`to_date` and the Flutter compatibility aliases `start_date`/`end_date` (`DD-MM-YYYY`); branch filters accept `branch_id` or the legacy `warehouse_id`. An employee is always restricted to the employee record's active branch; a submitted branch ID cannot override that assignment. Other users may access only branches allowed by their role/branch manager assignment. Product prices and sale totals are recalculated from server records. Sale completion uses the existing `SaleService` and inventory ledger.

## Current Flutter coverage and website-only pages

The API supports the screens already present in Flutter: dashboard and summary report; products, categories, brands and branch list; POS search, customer selection/add, coupon, checkout, drafts and invoice; sales, purchases and expenses lists; accounts, balance/deposit; profile, password, language and theme.

Website pages not added to Flutter in this scope remain website-only: gift cards, coupons and reward-point administration, delivery, quotations, bulk product import and barcode printing, inventory and stock history, transfers, purchase create/edit/details, sale/purchase returns, customer groups and suppliers management, account CRUD and statement, expense CRUD/categories, CRM, HRM, detailed reports (customer/supplier ledger, inventory, employees, branch), SMS logs/templates/settings, general/color/print/backup/mail settings, branches/roles/users administration, units/currencies/activity logs and Help/FAQ. User creation and role assignment stay on the website.

## Operational notes

- Run the website migrations before deploying; this adds the user-owned `mobile_pos_drafts` table. Sanctum's `personal_access_tokens` table must also be migrated.
- The website and mobile app must point to the same Laravel deployment and database. There is no mobile-side database replica or offline sale queue.
- A branch must have an active ledger account and the user must have the relevant website permissions. An owner/admin provisions users and branch assignment through website administration.
- Store production credentials and HTTPS configuration on the server. The mobile binary contains only the website origin; it does not contain server secrets.
