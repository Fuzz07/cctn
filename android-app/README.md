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

Bump `versionCode` (and usually `versionName`) in `app/build.gradle.kts` for
every release; Android refuses to install an APK whose `versionCode` is not
higher than the installed one.

## Distribution

The website serves the APK from `public/downloads/cctn-app.apk`, via
`/download-apk` (`HomeController::downloadApk`). On Vercel that route redirects
to the static path, because a serverless response is capped at 4.5 MB. Publishing
a new version means copying the **signed** release APK over that file.

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

## Backend prerequisites

The app is only as available as the API, and these were the findings when it
was built.

**On `bctibantayan.com`, every `/api/*` request returned `403 Forbidden`** — an
HTML error page, not JSON — while the website itself served normally. The cause
was a rule in the repository's root `.htaccess`:

```apache
RewriteRule ^(app|bootstrap|config|...|android-app|api)/ - [F,L]
```

It is there to keep the physical `api/` directory (the Vercel entry point) from
being served, but `/api/` is also the mobile API's route prefix, so it refused
every request the app makes. The rule now blocks only `api/index.php`, which
leaves Laravel's `/api/v1/...` routes reachable. **That fix has to be deployed
before the app can talk to this host**, and it is worth re-checking `/api/v1/services`
returns JSON (a 401 is the correct answer without a token) once it is.

**On the older `cctn-two.vercel.app` deployment**, two further problems were
visible, and are worth confirming on this host once the API responds:

1. **The database was unreachable** — every query failed with
   `SQLSTATE[HY000] [2006] MySQL server has gone away`, so login could not succeed.
2. **`APP_DEBUG` was on in production** — errors came back as a full stack trace
   including file paths and the failing SQL. It should be `false`.

The app degrades correctly in all of these cases: it reports that the server is
having trouble and never displays a 5xx body.
