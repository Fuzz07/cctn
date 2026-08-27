# BCTVI — customer app (Android)

The customer-facing app for the BCTVI network. It talks to the Laravel backend
in this repository over `/api/v1`, using the Sanctum bearer token issued at
login.

This is a customer app only. Nothing under `/admin` is reachable from it, and
no admin endpoint is declared in the API interface.

## What a customer can do

| Screen | Backed by |
| --- | --- |
| Sign in / register (3-step) | `POST /auth/login`, `POST /auth/register` |
| Home — balance, next visit, counts | `GET /appointments`, `GET /billing` |
| Bookings — list, cancel | `GET /appointments`, `DELETE /appointments/{id}` |
| Book a service — service, date, slot | `GET /services`, `GET /appointments/slots`, `POST /appointments` |
| Billing — balance and statements | `GET /billing` |
| Support — report a fault, track it | `GET /maintenance`, `POST /maintenance` |
| Assistant - chatbot help | `POST /chat` |
| Account — view/edit profile, password, sign out | `GET /profile`, `PUT /profile`, `POST /auth/logout` |

## Building

Java 17 and the Android SDK (API 34) are required. `local.properties` must
point at the SDK; it is not in version control.

```bash
./gradlew assembleDebug          # debug APK
./gradlew testDebugUnitTest      # unit tests
./gradlew lintRelease            # lint
./gradlew assembleRelease        # release APK (see signing below)
```

A debug build can be pointed at another API without editing any file:

```bash
./gradlew assembleDebug -PapiBaseUrl=http://10.0.2.2:8000/api/v1/
```

`10.0.2.2` is the host machine as seen from the emulator, so that is the
address for a local `php artisan serve`. Cleartext HTTP is permitted **only**
in debug builds (`app/src/debug/res/xml/network_security_config.xml`); the
release config refuses it outright and always uses the production API.

## Signing a release

Release signing is read from `keystore.properties` at the root of `android-app`.
That file, and any keystore, are gitignored — they must never be committed.

```bash
keytool -genkeypair -v -keystore bctvi-release.jks \
  -keyalg RSA -keysize 2048 -validity 10000 -alias bctvi
```

```properties
# android-app/keystore.properties
storeFile=bctvi-release.jks
storePassword=...
keyAlias=bctvi
keyPassword=...
```

Without that file the release build still succeeds and produces
`app-release-unsigned.apk`, so a fresh clone and CI are never blocked on a
secret. An unsigned APK **cannot be installed** — the file must be signed
before it is handed to customers.

### The current key

A release keystore has already been generated and used for `versionCode 2`:

```
file         android-app/bctvi-release.jks   (gitignored)
alias        bctvi
subject      CN=Bantayan Cable TV and Internet, O=BCTVI, L=Bantayan, ST=Cebu, C=PH
SHA-256      78:50:0F:B5:8A:D4:70:45:1F:46:D7:F0:A8:2A:B7:62:88:7A:E4:AE:D3:1F:6F:4E:F4:4A:9E:45:B8:DA:7D:DB
valid        10,000 days
```

**Back up `bctvi-release.jks` and `keystore.properties` somewhere outside this
folder.** They are deliberately not in version control, so nothing else holds a
copy. If that key is lost, every future update has to be signed with a new one,
and every customer has to uninstall and reinstall to take it — the same
one-time break described under Distribution. To use your own key instead, do it
now rather than later: replace the keystore, change the passwords in
`keystore.properties`, and rebuild.

Bump `versionCode` (and usually `versionName`) in `app/build.gradle.kts` for
every release; Android refuses to install an APK whose `versionCode` is not
higher than the installed one.

## Distribution

`/download-apk` (`HomeController::downloadApk`) is the one download button, on
the home page. It serves the APK from **two** places, and both have to be
updated together:

| File | Used by |
| --- | --- |
| `resources/apk/cctn-app.apk` | Hostinger and local — streamed by the controller |
| `public/downloads/cctn-app.apk` | the Vercel redirect, and any direct link |

```bash
cd android-app && ./gradlew assembleRelease
cp app/build/outputs/apk/release/app-release.apk ../public/downloads/cctn-app.apk
cp app/build/outputs/apk/release/app-release.apk ../resources/apk/cctn-app.apk
```

Keep the file name `cctn-app.apk` — the route and any links already point at it.

### The August 2026 build was signed with the Android debug key

Everything up to `versionCode 1` was distributed debug-signed, which means
anyone holding the standard debug keystore could have signed an update to it.
`versionCode 2` onwards is signed with the BCTVI release key below.

The consequence is that Android will not install this build over the old one —
it fails with `INSTALL_FAILED_UPDATE_INCOMPATIBLE`. **Anyone who installed the
earlier APK has to uninstall it once**, which is what the note under the
download button on the home page tells them. This is a one-time cost; updates
signed with the same key from here on install over each other normally.

## How it is put together

`MVVM over a repository layer`, single activity, Jetpack Compose.

```
core/        AppResult + AppError, validators, formatters, service area data
data/
  remote/    Retrofit interface, DTOs, interceptors, error mapping
  local/     SessionStore — encrypted token and cached profile
  session/   SessionManager — the single source of truth for who is signed in
  repo/      One repository per API area, all returning AppResult
di/          Hilt bindings for OkHttp, Retrofit, Json
ui/          Theme, shared components, navigation, one package per screen
```

A few decisions worth knowing about:

- **Which graph is shown is driven only by `SessionState`.** A token the server
  rejects is cleared by `UnauthorizedInterceptor` on the first 401, and the app
  falls back to the login screen without any screen handling it.
- **5xx response bodies are never shown to the user.** The backend currently
  runs with debug output enabled and its 500 payload can contain a stack trace
  and the failing SQL, so only 4xx messages — which are written for the client —
  are passed through. There is a test covering this.
- **Client-side validation mirrors `app/Support/InputRules.php`** so a mistake is
  caught as the user types. The server rules remain the ones that protect the
  database; if they change there, change `core/Validators.kt` too.
- **Insets are handled once**, by the app-level column in `ui/CctnApp.kt`. Every
  screen's `Scaffold` sets `contentWindowInsets = WindowInsets(0)` so nothing is
  applied twice.
- **`surfaceTint` is set to the surface colour.** Material 3 tints raised
  surfaces with the primary colour by default, which with a primary this red
  turns every card and the navigation bar pink.

## Backend notes

The API on `bctibantayan.com` is healthy: `/api/v1/services` answers `401
Unauthenticated` without a token, and a bad login answers `422` with per-field
errors and no stack trace — so the database is reachable and `APP_DEBUG` is off.

Two things are still worth knowing.

**Do not put `api` back in the blocked-prefix rule.** The root `.htaccess`
refuses application folders over HTTP, and `api` was in that list:

```apache
RewriteRule ^(app|bootstrap|config|...|android-app|api)/ - [F,L]
```

It was meant to hide the physical `api/` directory (the Vercel entry point), but
`/api/` is also the mobile API's route prefix, so that rule returns `403
Forbidden` for every request this app makes. It now blocks `api/index.php`
alone, which hides the entry point and leaves `/api/v1/...` reachable. Deploying
a copy of `.htaccess` without that change would take the mobile app offline
while leaving the website working — a failure that is easy to misread.

**The older `cctn-two.vercel.app` deployment was in worse shape** when this app
was built: its database was unreachable (`SQLSTATE[HY000] [2006] MySQL server has
gone away`) and `APP_DEBUG` was on, so errors came back as stack traces with the
failing SQL. Neither applies to `bctibantayan.com`, but if that Vercel
deployment is still live it is serving a broken API and leaking internals.

The app degrades correctly in all of these cases: it reports that the server is
having trouble and never displays a 5xx body.
