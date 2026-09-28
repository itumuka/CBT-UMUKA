# 🚀 Docker Setup untuk CBT UMUKA

Setup Docker lengkap untuk aplikasi CBT berbasis Laravel dengan MySQL, Redis, Nginx, dan PHP-FPM.

## 📋 Prerequisites

- Docker Engine 20.10+
- Docker Compose 2.0+
- Minimal 4GB RAM
- Port tersedia: 8006 (App), 33006 (MySQL), 63006 (Redis)

## 🏗️ Arsitektur

- **cbt-app**: Laravel 10 + PHP 8.1-FPM + Nginx + Node.js 18
- **cbt-mysql**: MySQL 8.0
- **cbt-redis**: Redis 7

## 🚀 Quick Start

### 1. Setup Environment

```bash
# Copy template environment untuk Docker
cp .env.docker .env

# Edit sesuai kebutuhan
nano .env
```

### 2. Build & Run

```bash
# Build images
docker-compose build

# Start semua services
docker-compose up -d

# Lihat logs
docker-compose logs -f
```

### 3. Setup Aplikasi

```bash
# Masuk ke container
docker exec -it cbt-laravel bash

# Generate app key (jika belum)
php artisan key:generate

# Run migrations
php artisan migrate --seed

# Create storage link
php artisan storage:link

# Clear cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Exit container
exit
```

### 4. Akses Aplikasi

- **Web**: http://localhost:8006
- **MySQL**: localhost:33006 (user: cbt_user, pass: cbt_password)
- **Redis**: localhost:63006

## 📁 Struktur Docker

```
cbt/
├── Dockerfile              # Image definition
├── docker-compose.yml      # Services orchestration
├── .dockerignore          # Files to exclude
├── .env.docker            # Environment template
└── docker/
    ├── nginx.conf         # Nginx configuration
    ├── php-custom.ini     # PHP settings
    └── supervisord.conf   # Process manager
```

## 🔧 Commands Berguna

### Container Management

```bash
# Start services
docker-compose up -d

# Stop services
docker-compose down

# Restart services
docker-compose restart

# View logs
docker-compose logs -f [service-name]

# Rebuild specific service
docker-compose build --no-cache cbt-app
```

### Application Commands

```bash
# Run artisan commands
docker exec -it cbt-laravel php artisan [command]

# Composer install/update
docker exec -it cbt-laravel composer install

# NPM commands
docker exec -it cbt-laravel npm run build

# Database access
docker exec -it cbt-mysql mysql -u cbt_user -p
```

### Database Management

```bash
# Import database
docker exec -i cbt-mysql mysql -u cbt_user -pcbt_password db_cbt < backup.sql

# Export database
docker exec -i cbt-mysql mysqldump -u cbt_user -pcbt_password db_cbt > backup.sql

# Access MySQL shell
docker exec -it cbt-mysql mysql -u root -p
```

## 🔐 Security

- Ganti semua password default di `.env`
- Set `APP_DEBUG=false` untuk production
- Set `APP_ENV=production` untuk production
- Gunakan HTTPS dengan reverse proxy (Nginx/Traefik)

## 📊 Performance

- **OPcache**: Aktif untuk PHP optimization
- **Redis**: Digunakan untuk cache, session, dan queue
- **Queue Workers**: 2 workers berjalan via Supervisor
- **Scheduler**: Cron jobs Laravel otomatis via Supervisor

## 🐛 Troubleshooting

### Permission Issues

```bash
docker exec -it cbt-laravel chown -R www-data:www-data /var/www/storage
docker exec -it cbt-laravel chmod -R 775 /var/www/storage
```

### Clear All Cache

```bash
docker exec -it cbt-laravel php artisan cache:clear
docker exec -it cbt-laravel php artisan config:clear
docker exec -it cbt-laravel php artisan route:clear
docker exec -it cbt-laravel php artisan view:clear
```

### Database Connection Issues

```bash
# Check MySQL is running
docker-compose ps

# Test connection
docker exec -it cbt-laravel php artisan tinker
>>> DB::connection()->getPdo();
```

## 📝 Environment Variables

Key variables di `.env`:

```env
DB_HOST=cbt-mysql          # MySQL service name
DB_PORT=3306
DB_DATABASE=db_cbt
DB_USERNAME=cbt_user
DB_PASSWORD=cbt_password

REDIS_HOST=cbt-redis       # Redis service name
REDIS_PORT=6379

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

## 🔄 Update & Maintenance

```bash
# Pull latest code
git pull origin main

# Rebuild and restart
docker-compose down
docker-compose build --no-cache
docker-compose up -d

# Run migrations
docker exec -it cbt-laravel php artisan migrate

# Rebuild frontend
docker exec -it cbt-laravel npm run build
```

## 📦 Backup & Restore

### Backup

```bash
# Database
docker exec -i cbt-mysql mysqldump -u cbt_user -pcbt_password db_cbt > backup-$(date +%Y%m%d).sql

# Files
tar -czf uploads-backup-$(date +%Y%m%d).tar.gz public/upload/
```

### Restore

```bash
# Database
docker exec -i cbt-mysql mysql -u cbt_user -pcbt_password db_cbt < backup-20260311.sql

# Files
tar -xzf uploads-backup-20260311.tar.gz
```

## 📞 Support

Untuk issues dan pertanyaan, hubungi tim IT UMUKA.

---

**Last Updated**: March 2026
**Version**: 1.0.0
