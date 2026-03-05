# Ship Readiness Checklist

Use this checklist as the pre-release gate for CB Market Hub.

## 1) Environment and secrets

- [ ] Copy `.env.production.example` to your deployment secret store and set real values.
- [ ] Generate and set a unique `APP_KEY` for each environment.
- [ ] Confirm `APP_ENV=production` and `APP_DEBUG=false`.
- [ ] Confirm `APP_URL` points at the canonical public URL.
- [ ] Configure SMTP credentials and verify outbound mail.
- [ ] Configure DB/Redis credentials with least privilege users.

## 2) Build and deploy

- [ ] Install PHP and JS dependencies in CI (`composer install --no-dev`, `npm ci`).
- [ ] Build frontend assets (`npm run build`).
- [ ] Run migrations (`php artisan migrate --force`).
- [ ] Publish storage symlink (`php artisan storage:link`) where public uploads are required.
- [ ] Cache config/routes/views for production:
  - [ ] `php artisan config:cache`
  - [ ] `php artisan route:cache`
  - [ ] `php artisan view:cache`

## 3) Runtime services

- [ ] Run queue workers under a process manager (systemd/supervisor/container orchestrator).
- [ ] Verify queue throughput and failed job handling.
- [ ] Confirm cron scheduler is running (`php artisan schedule:run` every minute).
- [ ] Confirm logs are shipped and retained.

## 4) Application protections

- [ ] Verify throttling is active for chat, votes, reactions, comments, and reports.
- [ ] Verify moderation routes are inaccessible to non-admin users.
- [ ] Verify authenticated write routes reject unauthenticated users.
- [ ] Review any public endpoints for abuse and scraping tolerance.

## 5) Data safety and recovery

- [ ] Run automated DB backups on a tested schedule.
- [ ] Keep backup retention policy documented.
- [ ] Perform at least one restore drill to a staging environment.

## 6) Observability and alerting

- [ ] Uptime checks for `/` and `/up` health route.
- [ ] Alerts for HTTP 5xx rate, queue failures, and DB/Redis connectivity.
- [ ] Error tracking configured for production exceptions.

## 7) Release validation (staging smoke test)

- [ ] Authentication: register/login/logout/password reset.
- [ ] Posts, polls, listings, events, suggestions creation/edit/delete paths.
- [ ] Chat send/fetch/delete/report flow.
- [ ] Notifications mark single/read all.
- [ ] Admin moderation status update flow.

## 8) Rollback plan

- [ ] Define rollback trigger conditions.
- [ ] Keep previous deploy artifact available.
- [ ] Document safe rollback steps for code + migrations.
