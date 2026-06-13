# Deployment Guide — Lombok Nature Culture
**Server:** Hostinger VPS (Ubuntu 22.04)  
**Stack:** Nginx + PHP-FPM 8.2 + PostgreSQL 15 + Laravel 12 + Filament

---

## 1. Server Prerequisites

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Nginx
sudo apt install nginx -y

# Install PHP 8.2 + extensions
sudo add-apt-repository ppa:ondrej/php -y
sudo apt install php8.2-fpm php8.2-pgsql php8.2-mbstring php8.2-xml \
  php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath php8.2-intl -y

# Install PostgreSQL
sudo apt install postgresql postgresql-contrib -y

# Install Supervisor (for queue workers)
sudo apt install supervisor -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

---

## 2. PostgreSQL Setup

```bash
sudo -u postgres psql <<EOF
CREATE USER lnc_user WITH PASSWORD 'REPLACE_WITH_STRONG_PASSWORD';
CREATE DATABASE lnc_production OWNER lnc_user;
GRANT ALL PRIVILEGES ON DATABASE lnc_production TO lnc_user;
EOF
```

---

## 3. Deploy Project Files

```bash
# Create directory structure
sudo mkdir -p /var/www/lnc/backups
sudo chown -R www-data:www-data /var/www/lnc

# Clone repository
cd /var/www/lnc
sudo -u www-data git clone git@github.com:YOUR_ORG/lnc-v2.git .

# Or if already cloned, just pull
sudo -u www-data git pull origin main
```

---

## 4. Configure PHP Frontend Environment

```bash
# Copy and configure
sudo cp /var/www/lnc/deployment/.env.production.example /var/www/lnc/public_html/.env
sudo nano /var/www/lnc/public_html/.env
# Fill in real DB_PASS, MIDTRANS keys, DEPLOY_SECRET

sudo chmod 640 /var/www/lnc/public_html/.env
sudo chown www-data:www-data /var/www/lnc/public_html/.env
```

---

## 5. Install Laravel Backend

```bash
cd /var/www/lnc/backend

# Copy environment
sudo -u www-data cp .env.example .env
sudo nano .env  # Fill in DB credentials, APP_KEY will be generated next

# Install dependencies
sudo -u www-data composer install --no-dev --optimize-autoloader

# Generate app key
sudo -u www-data php artisan key:generate

# Run migrations
sudo -u www-data php artisan migrate --force

# Seed database (packages, hotels, team, testimonials from data.php)
sudo -u www-data php artisan db:seed --force

# Create admin user (set ADMIN_PASSWORD in .env first)
sudo -u www-data php artisan db:seed --class=DatabaseSeeder --force

# Optimize
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# Storage link
sudo -u www-data php artisan storage:link

# Set permissions
sudo chmod -R 775 /var/www/lnc/backend/storage
sudo chmod -R 775 /var/www/lnc/backend/bootstrap/cache
sudo chown -R www-data:www-data /var/www/lnc/backend
```

---

## 6. Configure Nginx

```bash
sudo cp /var/www/lnc/deployment/nginx.conf /etc/nginx/sites-available/lomboknatureculture.com
sudo ln -s /etc/nginx/sites-available/lomboknatureculture.com /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default

# Test configuration
sudo nginx -t

# Reload
sudo systemctl reload nginx
```

---

## 7. Configure PHP-FPM

```bash
sudo cp /var/www/lnc/deployment/php-fpm.conf /etc/php/8.2/fpm/pool.d/lnc.conf
sudo systemctl restart php8.2-fpm
```

---

## 8. Configure Supervisor (Queue Workers)

```bash
sudo cp /var/www/lnc/deployment/supervisor.conf /etc/supervisor/conf.d/lnc-queue.conf
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start lnc-queue:*
sudo supervisorctl status
```

---

## 9. Set Up Cron

```bash
sudo crontab -u www-data -e
# Add content from /var/www/lnc/deployment/cron.conf
```

---

## 10. SSL (Let's Encrypt)

```bash
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d lomboknatureculture.com -d www.lomboknatureculture.com
# Follow prompts — certbot will update nginx.conf automatically
sudo certbot renew --dry-run  # Test auto-renewal
```

---

## 11. Initialize Database Tables (PHP Frontend)

```bash
# After PostgreSQL is configured, visit:
# https://lomboknatureculture.com/create-tables.php?token=LNC-DB-SETUP-2026
# This creates all tables if run before Laravel migrations
# OR run: php artisan migrate (preferred)
```

---

## 12. Set Deploy Webhook Secret

```bash
# Generate secret
openssl rand -hex 32

# Add to /var/www/lnc/public_html/.env:
# DEPLOY_SECRET=<the generated secret>

# Add to GitHub repository secrets:
# Settings → Secrets and Variables → Actions → New repository secret
# Name: DEPLOY_SECRET
# Value: <same secret>
```

---

## Admin Panel Access

- URL: `https://lomboknatureculture.com/admin`
- Create admin user: `php artisan make:filament-user`
- Or set `ADMIN_EMAIL` and `ADMIN_PASSWORD` in `.env` before seeding

---

## Troubleshooting

| Issue | Fix |
|-------|-----|
| 502 Bad Gateway | `sudo systemctl status php8.2-fpm` |
| Permission denied | `sudo chown -R www-data:www-data /var/www/lnc` |
| DB connection refused | Check `.env` DB_PASS, test: `psql -U lnc_user -h 127.0.0.1 lnc_production` |
| Midtrans sandbox not working | Check `MIDTRANS_IS_PRODUCTION=false` and sandbox keys |
| Queue not processing | `sudo supervisorctl restart lnc-queue:*` |
| OPcache stale after deploy | Included in GitHub Actions workflow |
