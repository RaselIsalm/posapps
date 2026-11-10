# POS Apps

ONEPOS backend and Flutter Android app.

## Downloads

- [Download the complete source as a ZIP](https://github.com/sbayshop3171-ship-it/posapps/archive/refs/heads/main.zip)
- [Download the Android APK](https://github.com/sbayshop3171-ship-it/posapps/raw/refs/heads/main/downloads/onepos-release.apk)
- [Download the Android App Bundle (AAB)](https://github.com/sbayshop3171-ship-it/posapps/raw/refs/heads/main/downloads/onepos-release.aab)

The APK can be installed directly on a compatible Android device. The AAB is for Google Play Console. See [`mobile/README.md`](mobile/README.md) for local development and build commands.

## Project layout

- `app/`, `routes/`, `resources/`, and `database/` — Laravel application
- `mobile/` — Flutter Android client
- `docs/` — API documentation
- `deploy/` — deployment package and notes
- `downloads/` — downloadable Android release files

Local environment files, signing keys, caches, and databases are intentionally excluded from source control. Create them from the example/configuration instructions before running the application.
