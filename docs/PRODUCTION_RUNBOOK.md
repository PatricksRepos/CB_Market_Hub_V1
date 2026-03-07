# Production Runbook (CB Community Post)

This is the minimum release gate to ship safely.

## 1) Pre-deploy checks

1. `composer install --no-dev --optimize-autoloader`
2. `npm ci`
3. `npm run build`
4. `php artisan test`

## 2) Required environment values

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL` set to canonical URL
- `APP_KEY` unique and generated (`php artisan key:generate --show`)
- DB/Redis/SMTP credentials set from secret manager

Use `.env.production.example` as the template.

## 3) Deploy commands

1. `php artisan migrate --force`
2. `php artisan storage:link` (first deploy only, or if missing)
3. `php artisan config:cache`
4. `php artisan route:cache`
5. `php artisan view:cache`

## 4) Runtime services

- Queue worker managed by systemd/supervisor/orchestrator:
  - `php artisan queue:work --tries=3 --timeout=90`
- Scheduler every minute:
  - `* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1`

## 5) Smoke test after deploy

- Login/logout/register/password reset
- Create and view post, poll, listing, suggestion, event
- Buyer clicks Message Seller and confirms private thread opens
- Seller replies and buyer receives reply
- Connect nav opens private contacts and public chat access points
- Notifications increment and can be marked read

## 6) Rollback

1. Re-deploy prior artifact/tag
2. Run rollback migration if needed (`php artisan migrate:rollback --step=1` only when safe)
3. `php artisan config:clear && php artisan route:clear && php artisan view:clear`
