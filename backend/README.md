# TV Time Dashboard Backend

Laravel backend for the TV Time Dashboard project.

## Stack

- Laravel `13.x`
- Filament `5.x`
- SQLite for local development by default
- PHPUnit via `php artisan test`
- Session/cookie auth for the same-origin React SPA

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

Do not commit `.env`, local SQLite files, database dumps, GDPR exports, uploaded imports, generated private JSON, or private cache files.

## API

Current routes:

- `GET /api/v1/status`
- `GET /api/v1/me`
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout`
- `POST /api/v1/invites/accept`
- `GET /api/v1/dashboard`
- `POST /api/v1/alerts/{alert}/read`
- `POST /api/v1/alerts/read-all`

The API uses session-backed same-origin authentication. Public registration is intentionally absent; users are created through invites or admin management.

`GET /api/v1/dashboard` returns the dashboard-compatible JSON shape consumed by the React app.

## Import Existing Data

Import an ignored private SQLite or generated JSON source for one existing user:

```bash
php artisan tvtime:import-user {user_id} ../var/private/tvtime.sqlite
```

Safety behavior:

- user must exist
- source file must exist
- source must be under `../var/private`, `../public/data`, `storage/app/private`, or `storage/app/imports`
- raw GDPR/token/device/IP data is never printed
- command output is limited to imported counts
- existing media rows for that one user are replaced; other users are untouched

## Admin

Filament is installed at `/admin`.

Current resources:

- Users
- Invites
- Alerts
- Shows
- Movies
- Episode Watches
- Movie Watches
- Analytics Events
- Audit Logs

Only active `owner` and `admin` users can access the Filament panel. Imported media resources are inspection-first/read-only to keep user-owned library data explicit and safe.

## Tests

```bash
php artisan test
```

The feature tests cover status readiness, invite acceptance, login/logout, `/me`, unauthenticated private API access, empty and imported dashboard payloads, alert read persistence, analytics events, audit logs, import validation, and cross-user isolation.

## Deployment Checklist

Do not deploy private files. Keep Apache Basic Auth enabled on staging. After code is reviewed on the server, configure `.env`, run migrations with `php artisan migrate --force`, run `php artisan filament:assets` if Composer did not publish Filament assets, build frontend assets from the repo root, and smoke test `/api/v1/status`, login, `/api/v1/dashboard`, alert read actions, and `/admin`.
