# ONEPOS Android app server setup

The Android app loads the website `/login` page at startup. Laravel detects the `ONEPOSApp/1.0` user-agent and renders app-only controls on that existing login card: Base URL above the credential fields and Demo/User mode selectors beneath Sign in. Normal browser login remains unchanged.

- **User mode** starts selected. The user enters an origin or full `/login` URL, username/email and password. The app validates the HTTPS/public host, requires a compatible `/api/v1/mobile/bootstrap` response, then posts credentials to `/api/v1/mobile/login`. A one-use ticket is exchanged at `/mobile/session/exchange` inside that server's WebView, creating the normal Laravel web session.
- **Demo mode** contacts only `https://pos.onetech.bd` at `/api/v1/demo/guest-session`, exchanges the ticket at `/mobile/session/exchange`, and enters the separately seeded read-only demo database. No demo password is placed in the APK.

After successful User authentication, the app stores only the selected server origin. WebView keeps the website cookie. Logout clears the WebView cookie but keeps the selected origin; Change domain returns to the default login page and clears the stored origin; Reset returns to `https://pos.onetech.bd`. Each customer server uses its own independent OnePOS installation and database.

Custom User requires these endpoints on each customer deployment: `GET /api/v1/mobile/bootstrap`, `POST /api/v1/mobile/login`, and `POST /mobile/session/exchange`. The main server additionally needs the Demo endpoint and isolated database configuration below. API login `POST /api/v1/auth/login` remains available to API clients and continues to return Sanctum tokens.
## Configure the isolated demo database

The demo database must be a separate database from the live production database. Configure these server environment values before enabling demo access:

```dotenv
ONEPOS_DEMO_GUEST_ENABLED=true
ONEPOS_DEMO_GUEST_EMAIL=demo-guest@pos.onetech.bd
DEMO_DB_CONNECTION=mysql
DEMO_DB_HOST=127.0.0.1
DEMO_DB_PORT=3306
DEMO_DB_DATABASE=onepos_demo
DEMO_DB_USERNAME=onepos_demo_user
DEMO_DB_PASSWORD=replace-with-a-private-password
```

Create and seed the isolated database, then run the normal migration on the production database:

```sh
php artisan migrate --force
php artisan migrate --database=demo --force
php artisan db:seed --database=demo --class=Database\\Seeders\\DemoInstanceSeeder --force
php artisan config:cache
php artisan route:cache
```

The demo seeder creates a dedicated `Demo-Guest` account with a random unusable password and sample inventory/sales records. The app receives a short-lived, one-use ticket; the ticket is exchanged for a web session. Demo writes are blocked on both web and API requests. Keep demo access disabled until the separate database is migrated and seeded.

Each custom customer server needs the normal production migration, including the mobile guest-ticket migration. Demo guest access stays disabled on those servers unless they are explicitly configured with their own isolated demo database.

## Build the app

Release builds use `https://pos.onetech.bd` as the Demo / Guest server and accept any public HTTPS domain for Custom User mode. The app opens `/login` directly and does not need a customer-specific `BASE_URL`:

```sh
cd mobile
flutter pub get
flutter build appbundle --release
```

HTTP and local/private addresses remain available only in debug builds for development. Never put a demo password or customer credentials in the APK.
