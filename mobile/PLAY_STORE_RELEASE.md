# ONEPOS Play Store release notes

## Listing draft

- **App name:** ONEPOS
- **Package ID:** `bd.onetech.pos`
- **Short description:** Manage sales, inventory, purchases and reports with ONEPOS.
- **Full description:**

  ONEPOS helps store owners and authorized staff manage day-to-day business operations from one workspace. Sign in to access sales, products, inventory, purchases, customers, accounts and business reports. Your ONEPOS account and permissions control which features are available.

  The Android app connects securely to the ONEPOS service. An internet connection and an authorized ONEPOS account are required.

- **Privacy policy:** `https://pos.onetech.bd/privacy`
- **Account deletion:** `https://pos.onetech.bd/account-deletion`
- **Website ownership permission:** retain written ONETECH authorization with the Play Console release records.

## Assets and review access

- Use the generated Android launcher icon in `android/app/src/main/res/`.
- Capture phone screenshots from the release build after signing in with a non-production reviewer account. Include dashboard, product list, quick sale, and reports; do not show customer personal data, credentials, or real business records.
- Add a feature graphic and final screenshots in Play Console.
- In **App access**, give Google a working demo login and steps to reach the core POS workflows. Do not embed reviewer credentials in the app or repository.
- Configure Play App Signing and upload `build/app/outputs/bundle/release/app-release.aab`.

## Policy and release checklist

- Deploy the new Laravel migration and privacy/deletion routes before review: `php artisan migrate --force`.
- Confirm the public privacy and deletion URLs work over HTTPS on the production host.
- Confirm the configured email service sends account verification messages and the Super Admin can review deletion requests.
- Complete Data safety from the actual production data flows and configured hosting, mail, SMS, backup, and other providers.
- Complete content rating, target audience, app access, data deletion, and store listing sections in Play Console.
- Run the signed release on supported Android devices and internal/closed tracks before requesting production review.

## Local build commands

```sh
flutter run --dart-define=BASE_URL=http://10.0.2.2:8000
flutter build apk --debug --target-platform android-arm64 --dart-define=BASE_URL=http://<MAC_LAN_IP>:8000
flutter build apk --release --target-platform android-arm64 --dart-define=BASE_URL=https://pos.onetech.bd
flutter build appbundle --release
```

The first command is for an Android emulator; substitute the Mac's LAN IP for a physical Samsung phone. The local debug APK and signed live APK share a package ID but use different signatures, so they cannot replace one another without uninstalling the currently installed app. Production builds use the HTTPS ONEPOS host.
