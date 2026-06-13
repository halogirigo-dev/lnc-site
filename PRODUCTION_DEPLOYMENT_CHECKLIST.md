# Lombok Nature Culture — Production Deployment Checklist

## Phase 0 — Backup First

Before touching production:

```bash
cd /var/www

tar -czf lnc-backup-$(date +%F-%H%M).tar.gz lnc/
```

If PostgreSQL already contains data:

```bash
pg_dump lnc_db > lnc-db-backup-$(date +%F-%H%M).sql
```

---

## Phase 1 — Pull Latest Code

```bash
cd /var/www/lnc

git status
git pull origin main
```

Verify:

```bash
git log -1 --oneline
```

Confirm latest commit deployed.

---

## Phase 2 — Backend Dependencies

```bash
cd /var/www/lnc/backend

composer install \
  --no-dev \
  --prefer-dist \
  --optimize-autoloader
```

Verify:

```bash
php artisan about
```

Database connection must show OK.

---

## Phase 3 — Environment Verification

Verify:

```bash
cat .env
```

Check:

```env
APP_ENV=production
APP_DEBUG=false

DB_CONNECTION=pgsql

DB_HOST=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```

Never deploy with:

```env
APP_DEBUG=true
```

---

## Phase 4 — Database Migration

Preview first:

```bash
php artisan migrate:status
```

Run migrations:

```bash
php artisan migrate --force
```

Verify:

```bash
php artisan migrate:status
```

All migrations should be marked as Ran.

---

## Phase 5 — Optimization

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan optimize
```

---

## Phase 6 — Queue & Services

Restart queues:

```bash
php artisan queue:restart
```

Reload PHP-FPM:

Ubuntu/Debian:

```bash
sudo systemctl reload php8.4-fpm
```

AlmaLinux/Rocky:

```bash
sudo systemctl reload php-fpm
```

Verify:

```bash
systemctl status php-fpm
```

or

```bash
systemctl status php8.4-fpm
```

---

## Phase 7 — Filament Verification

Open:

https://admin.lomboknatureculture.com

Verify:

✓ Login works

✓ Dashboard widgets load

✓ Booking list loads

✓ Customer list loads

✓ No 500 errors

---

## Phase 8 — Booking Flow Test

Create test booking from:

https://lomboknatureculture.com/booking.php

Verify:

✓ Customer created

✓ Booking created

✓ Status log created

✓ Booking reference generated

✓ Appears in Filament

✓ Appears in dashboard counters

---

## Phase 9 — Operations Dashboard Verification

Verify widgets:

✓ Today's Arrivals

✓ Tomorrow's Arrivals

✓ Active Tours

✓ Pending Contact

✓ Pending Quote

✓ Pending Confirmation

Verify counts are correct.

---

## Phase 10 — Production Health Check

Verify:

```bash
curl -I https://lomboknatureculture.com
curl -I https://admin.lomboknatureculture.com
```

Check:

* HTTP 200
* SSL valid
* No redirect loops

Review logs:

```bash
tail -100 storage/logs/laravel.log
```

Must contain no critical errors.

---

## Go-Live Success Criteria

✓ Website accessible

✓ Admin accessible

✓ PostgreSQL connected

✓ 18 migrations applied

✓ Dashboard widgets working

✓ Booking flow working

✓ Status transitions working

✓ Audit logs working

✓ No critical errors in logs

Only after all checks pass should the deployment be considered successful.
