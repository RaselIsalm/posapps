ONE POS Laravel API v1.2 - cPanel deployment bundle

Target
  Laravel project root: /home/easypick/pos.onetech.bd
  Expected PHP: 8.4+ (the server currently reports PHP 8.4.27)
  The package contains application source only. It contains no .env, credentials,
  database dump, or user data.

IMPORTANT
  Take a fresh cPanel database backup and source-file backup before deploying.
  This package has not been deployed. Deploy from cPanel Terminal only after
  reviewing the backup and commands below. Do not import the local anonymized DB.

Before upload, the cPanel Terminal backup commands are:
  cd ~/pos.onetech.bd
  stamp=$(date +%Y%m%d-%H%M%S)
  tar -czf ~/onepos-api-predeploy-$stamp.tar.gz app bootstrap routes database/migrations
  php artisan --version
  php artisan route:list --path=api/v1

Upload this ZIP to ~/pos.onetech.bd, then run:
  cd ~/pos.onetech.bd
  unzip -o ONEPOS-API-v1.2-mobile-modules.zip
  php artisan optimize:clear
  php artisan migrate:status

Review the pending migrations. Only the following additive/mobile compatibility
migrations are expected from this release:
  2026_11_05_000000_create_mobile_pos_drafts_table
  2026_11_07_000000_ensure_payroll_payment_method_accepts_account_ids

If the pending list is correct, apply migrations:
  php artisan migrate --force
  php artisan route:list --path=api/v1
  curl -i -H 'Accept: application/json' https://pos.onetech.bd/api/v1/auth/me

Expected unauthenticated response from auth/me: HTTP 401 JSON, not 404/500.
Expected new quotation endpoint before authentication: HTTP 401 JSON, not 404.
Do not put a bearer token/password into a shared terminal transcript.

Rollback
  Restore app/, bootstrap/, routes/, and database/migrations/ from the backup
  tarball. If the two release migrations were applied, inspect the last migration
  batch before using `php artisan migrate:rollback --step=2`; the draft table
  rollback deletes saved mobile drafts. The payroll compatibility migration is
  intentionally forward-only. Restore the database backup if full rollback is
  needed. Never rollback unrelated migration batches.

After deployment
  Confirm routes and test the unauthenticated endpoints above. Then install the
  internal HTTPS APK (ONEPOS-live-internal-arm64.apk). Local HTTP APK is only for
  a computer/device on the developer's Wi-Fi and will not work on public mobile
  networks.
