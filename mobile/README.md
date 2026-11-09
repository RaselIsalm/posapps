# ONEPOS Android app

This Flutter app loads the existing ONEPOS mobile website inside an Android WebView. The site owns sign-in, routing, and its bottom navigation. Laravel's existing login session cookie is kept by Android WebView so opening `/login` returns an authenticated user to the dashboard.

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

Production builds default to `https://pos.onetech.bd`. Release builds reject HTTP and other hostnames.

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

The bundle is written to `build/app/outputs/bundle/release/app-release.aab`. Back up the upload keystore and its credentials securely. Configure Play App Signing and provide reviewer access credentials in Play Console.
