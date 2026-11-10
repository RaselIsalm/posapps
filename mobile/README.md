# ONEPOS Android app

The Android app opens the website `/login` page immediately. Laravel detects the `ONEPOSApp/1.0` WebView user-agent and renders the app-only Base URL field above credentials plus side-by-side Demo and User mode buttons beneath Sign in. Browser requests keep the existing login form and do not receive these controls. User mode validates the selected public HTTPS server with `/api/v1/mobile/bootstrap`, requests a short-lived one-use ticket at `/api/v1/mobile/login`, then exchanges it inside WebView for the website session. Demo mode requests a read-only guest ticket from `https://pos.onetech.bd`. Passwords and Sanctum tokens are not persisted; only the selected server origin is saved after successful User authentication, and WebView owns the session cookie.

The app adds an `ONEPOSApp/1.0` user-agent marker so Laravel disables Debugbar injection only for app requests. The app shell shows no page-navigation progress bar; site feedback for actions such as signing in or saving remains visible.

The Android launcher icon uses the red mark from `logo/app icon logo.png`; generated launcher sizes live under `android/app/src/main/res/`. The splash screen shows a four-second animated `ONEPOS` word reveal on a dark teal (`#1B6F65`) background before the WebView page is shown. The first letter is visible on the first Flutter frame so the animation does not begin with a blank color screen. The downloaded Techpost animation is retained as an unbundled reference at `assets/animations/techpost-source.json`; its vector wordmark cannot be relabeled in place.

The Android WebView adapter is locally patched to use `ACTION_OPEN_DOCUMENT` for file uploads. This avoids Android Photo Picker proxy URIs that can trigger `ERR_UPLOAD_FILE_CHANGED` in WebView; the picker grants access only to the file the user selects.

## Local development

Start Laravel so it is reachable from the Android emulator, then run:

```sh
flutter pub get
flutter run --dart-define=BASE_URL=http://10.0.2.2:8000
```

For a physical phone, use the computer's reachable LAN address instead of `10.0.2.2`, and make sure the Laravel dev server listens on the LAN interface. Cleartext HTTP is enabled only for Android debug builds.

Build and install a Samsung/ARM64 local test APK with:

```sh
flutter build apk --debug --target-platform android-arm64 \
  --dart-define=BASE_URL=http://<MAC_LAN_IP>:8000
adb install -r build/app/outputs/flutter-apk/app-debug.apk
```

Release builds use `https://pos.onetech.bd` as the initial login server and Demo / Guest server. Custom User accepts any public HTTPS server URL, checks `/api/v1/mobile/bootstrap`, and only then sends credentials to `/api/v1/mobile/login`. Release builds reject HTTP, IP literals, and private/local addresses. Server setup is documented in [`docs/mobile-app-setup.md`](../docs/mobile-app-setup.md).

Build a signed ARM64 live APK with:

```sh
flutter build apk --release --target-platform android-arm64 \
  --dart-define=BASE_URL=https://pos.onetech.bd
```

## Play Store release

The app uses package ID `bd.onetech.pos`, version `1.0.0+1`, minimum Android API 24, and target API 36. Flutter 3.38.1 sets API 24 as its minimum supported Android version.

Create a private upload key and keep the keystore and `android/key.properties` out of source control:

```sh
keytool -genkeypair -v \
  -keystore android/app/onepos-upload-key.jks \
  -keyalg RSA -keysize 2048 -validity 10000 \
  -alias onepos-upload
cp android/key.properties.example android/key.properties
```

Set the keystore path, password, alias, and key password in `android/key.properties`, then build the Play upload bundle:

```sh
flutter build appbundle --release
```

The Play Store build does not require a customer-specific `BASE_URL`; each user selects their OnePOS server in the app.

The bundle is written to `build/app/outputs/bundle/release/app-release.aab`. Back up the upload keystore and its credentials securely. Configure Play App Signing and provide reviewer access credentials in Play Console.
