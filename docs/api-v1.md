# ONEPOS mobile API v1

The Play Store Flutter app opens the website `/login` page directly. The `ONEPOSApp/1.0` marker enables app-only UI controls on that page; it grants no authentication or permissions. Custom User validates server compatibility, then exchanges credentials for a short-lived one-use web-session ticket. Demo tickets are available only on `https://pos.onetech.bd` and open an isolated read-only demo session. Browser login and `POST /api/v1/auth/login` remain unchanged.

`POST /api/v1/auth/login` remains available to API clients and returns a Sanctum token. The current WebView sign-in does not store or exchange that token. There is no public user registration; owners and administrators create users and grant roles in each customer's website deployment.

Live deployment check (2026-11-10): `https://pos.onetech.bd/login` returns HTTP 200 while the new mobile API routes are not yet deployed. Publish these backend changes and configure the isolated demo database before automatic production login can work.

## Authentication and response format

`POST /api/v1/auth/login` accepts `email` (email address or username) and `password`. It returns `data.access.token` and `data.user`. Tokens are Sanctum bearer tokens that expire after 30 days. Send `Authorization: Bearer <token>` and `Accept: application/json` on protected calls. `POST /auth/logout` revokes the current token. `GET /auth/me` refreshes the user and branch assignments. Inactive accounts are rejected and their current token is revoked.

Successful JSON responses use `{ "message": "...", "data": { ... } }`. Laravel validation errors return HTTP 422, unauthenticated/expired sessions return 401, permission or branch violations return 403, missing records return 404, and blocked deletions return 409. Paginated lists contain the named array plus `total`, `current_page`, `per_page`, and `last_page`.

## Implemented endpoints

| Method | Path | Permission / behavior |
|---|---|---|
| GET | `/mobile/bootstrap` | Public OnePOS/version compatibility descriptor; no user data; Custom User must validate this before sending credentials |
| POST | `/mobile/login` | Rate-limited; accepts `identifier` (email or username) and `password`, returns a one-use web-session ticket for an active user |
| POST | `/demo/guest-session` | Rate-limited; main server only, returns a short-lived one-use ticket for isolated read-only demo database |
| POST | `/mobile/session/exchange` | Consumes a one-use user or demo ticket, creates the Laravel web session, and redirects to the dashboard |
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

- Run the production migrations on every compatible customer deployment. Run `DemoInstanceSeeder` only against the separate demo database on the main deployment; see [`mobile-app-setup.md`](mobile-app-setup.md).
- Each customer URL points to that customer's independent OnePOS deployment and database. The app has no database replica or offline sale queue.
- A branch must have an active ledger account and the user must have the relevant website permissions. An owner/admin provisions users and branch assignment through website administration.
- Store production credentials and HTTPS configuration on each server. The app saves only the selected origin and leaves the customer's password and web session cookie to the website/WebView.
