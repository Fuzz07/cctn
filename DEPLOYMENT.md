# Deploying BCTVI to Hostinger

This project is a Laravel 9 app. These steps put it on Hostinger shared hosting
(hPanel). Everything lives inside `public_html`; the root `.htaccess` routes
requests into `public/`, so you do **not** need to change the document root.

---

## 1. Set the PHP version

hPanel → **Advanced → PHP Configuration** → select **PHP 8.1** or **8.2**.

Under the *PHP extensions* tab make sure these are enabled (they are by default):
`bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `curl`, `gd`.

## 2. Create the database

hPanel → **Databases → MySQL Databases**. Create a database and user, grant all
privileges, and note the name, user, and password — they all start with your
account prefix, e.g. `u123456789_bctvi`.

## 3. Upload the project

**With SSH** (Business plans and above — easiest):

```bash
cd ~/domains/your-domain.com/public_html
git clone https://github.com/mameowshiii/cctn.git .
```

**Without SSH:** download the repository as a ZIP, then hPanel → **Files → File
Manager** → open `public_html` → upload the ZIP → right-click → *Extract*. Make
sure the files land directly in `public_html`, not in a nested `cctn/` folder.

## 4. Install dependencies

`vendor/` is not in the repository, so it has to be installed.

**With SSH:**

```bash
composer install --no-dev --optimize-autoloader
```

**Without SSH:** run `composer install --no-dev --optimize-autoloader` on your own
machine, then upload the generated `vendor/` folder into `public_html`.

## 5. Configure the environment

Copy `.env.hostinger.example` to `.env` and fill in your domain, database
credentials, and mail settings. Then set the application key:

```bash
php artisan key:generate
```

No SSH? Generate one locally with the same command and paste the resulting
`APP_KEY=base64:...` line into the server's `.env` with File Manager.

Keep `APP_DEBUG=false` in production — it hides stack traces from visitors.

## 6. Set folder permissions

Laravel writes to these directories, and so do client photo uploads:

```bash
chmod -R 775 storage bootstrap/cache public/uploads
```

In File Manager: right-click each folder → *Permissions* → `775`, applying to
subfolders.

## 7. Run migrations and seeders

```bash
php artisan migrate --force
php artisan db:seed --force        # first deploy only: admin, plans, time slots
php artisan storage:link           # exposes uploaded payment proofs
```

The migrations include the client `proof_of_billing` column and the current plan
pricing (10–150 Mbps at ₱799–₱1,699 with free installation), so **the site will
not show the right plans until `migrate` has run.**

No SSH? Access `https://your-domain.com/migration.php` in your browser to run migrations and seeders remotely. Alternatively, export your local database to SQL and import it through hPanel → **Databases → phpMyAdmin**.

## 8. Cache the configuration

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Re-run these after any later change to `.env` or to routes. To undo:
`php artisan optimize:clear`.

## 9. Point Google sign-in at the live domain

In Google Cloud Console → **APIs & Services → Credentials** → your OAuth client,
add the authorized redirect URI:

```
https://your-domain.com/auth/google/callback
```

It must match `GOOGLE_REDIRECT_URI` in `.env` exactly, including `https`.

## 10. Enable HTTPS

hPanel → **Security → SSL** → install the free certificate. Then confirm
`APP_URL` in `.env` starts with `https://`.

Install the certificate **before** pointing traffic at the domain: the root
`.htaccess` already redirects every plain-HTTP request to `https://`, so without
a valid certificate visitors land on a browser security warning instead of the
site. The redirect leaves `/.well-known/` alone so certificate issuance and
renewal keep working over HTTP.

---

## After deploying

- **Uploads work here.** Client profile photos and proof-of-billing images are
  written to `public/uploads`, which is writable on Hostinger — unlike the Vercel
  deployment, where the filesystem is read-only and uploads fail.
- **The Android app** points at its own hard-coded URL in
  `android-app/app/src/main/java/com/cctn/app/MainActivity.kt`. Update that URL to
  your Hostinger domain and rebuild the APK if the app should use this server.
- **`vercel.json` and `api/index.php`** are only used by the Vercel deployment.
  They are harmless on Hostinger and blocked from direct access by `.htaccess`.

## Troubleshooting

| Symptom | Fix |
| --- | --- |
| Blank white page | `APP_KEY` is empty, or `storage/` is not writable. Check `storage/logs/laravel.log`. |
| 500 after changing `.env` | Run `php artisan config:clear`, then re-cache. |
| "No application encryption key" | Step 5 was skipped. |
| Plans show old prices | `php artisan migrate --force` has not been run. |
| Photo uploads fail | `public/uploads` is not writable — re-apply `775`. |
| CSS or images missing | Confirm the root `.htaccess` uploaded; hidden files must be visible in File Manager. |
| "Too many redirects" | Turn *off* hPanel's own **Force HTTPS** — the root `.htaccess` already does it, and running both can loop. |
| App shows a blank page but the site works | The app refuses plain HTTP. Every URL the site emits must be `https://`; check `APP_URL`. |
