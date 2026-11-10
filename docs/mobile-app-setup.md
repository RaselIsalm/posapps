# ONEPOS Android app server setup

The Play Store app supports two entry modes:

- **Demo / Guest** first opens `https://pos.onetech.bd/login`, then exchanges a short-lived guest ticket for a restricted demo session when the main server has guest access enabled. No guest password is placed in the APK or stored on the device. If guest access is not configured, the normal login page remains available.
- **Custom User** accepts a server domain or full `/login` URL and opens that server's `/login` page directly. It does not call the mobile bootstrap API before showing login. The app stores the server origin only; website cookies keep the web session.

Each customer server must expose a working HTTPS `/login` page. No customer database is shared with another installation.

The Android app calls `POST /api/v1/demo/guest-session` after selecting Demo / Guest, and exchanges the one-use ticket at `POST /demo/session/exchange`. The login page is loaded first; a successful ticket exchange then starts the guest session. Both routes require deployment and a configured, isolated demo database for automatic guest entry to work.

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
