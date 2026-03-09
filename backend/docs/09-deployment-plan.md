# HormoneLens — Deployment Plan

> Step-by-step deployment guide for the HormoneLens AI hormone simulation platform.

---

## Prerequisites

| Requirement | Version | Purpose |
|-------------|---------|---------|
| PHP | >= 8.2 | Laravel 12 runtime |
| Composer | >= 2.x | PHP dependency management |
| Node.js | >= 18.x | Vite frontend build |
| npm | >= 9.x | Frontend package management |
| Database | MySQL 8+ / PostgreSQL 15+ / SQLite | Data persistence |
| Redis | >= 6.x | Caching (optional but recommended) |
| AWS Account | Active | Bedrock AI, S3, SES, CloudWatch |
| Web Server | Nginx / Apache | Production HTTP server |
| SSL Certificate | Valid | HTTPS (required for Sanctum SPA) |

---

## 1. Server Setup

### 1.1 System Packages

```bash
# Ubuntu/Debian
sudo apt update && sudo apt install -y \
  php8.2 php8.2-fpm php8.2-cli php8.2-common \
  php8.2-mysql php8.2-pgsql php8.2-sqlite3 \
  php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip \
  php8.2-bcmath php8.2-redis php8.2-gd \
  nginx redis-server supervisor unzip git
```

### 1.2 Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 1.3 Node.js

```bash
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs
```

---

## 2. Application Deployment

### 2.1 Clone Repository

```bash
cd /var/www
git clone <repository-url> hormonelens
cd hormonelens/backend
```

### 2.2 Install Dependencies

```bash
# PHP dependencies
composer install --no-dev --optimize-autoloader

# Frontend dependencies
npm ci
```

### 2.3 Build Frontend Assets

```bash
npm run build
```

This builds 4 entrypoints:
- `resources/css/app.css`
- `resources/js/app.js`
- `resources/js/dashboard-twin.jsx` (React + Three.js)
- `resources/js/onboarding-app.jsx` (React)

---

## 3. Environment Configuration

### 3.1 Create Environment File

```bash
cp .env.example .env
php artisan key:generate
```

### 3.2 Configure Environment Variables

Edit `.env` with production values:

```env
# ── Application ──
APP_NAME="HormoneLens AI"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# ── Database ──
DB_CONNECTION=mysql          # or pgsql
DB_HOST=your-rds-endpoint.amazonaws.com
DB_PORT=3306                 # 5432 for PostgreSQL
DB_DATABASE=hormonelens
DB_USERNAME=hormonelens_user
DB_PASSWORD=<secure-password>

# ── Cache / Queue / Session ──
CACHE_STORE=redis            # recommended for production
QUEUE_CONNECTION=redis       # or database
SESSION_DRIVER=redis         # or database

# ── Redis ──
REDIS_HOST=your-elasticache-endpoint.amazonaws.com
REDIS_PORT=6379
REDIS_PASSWORD=<redis-auth-token>

# ── Sanctum (SPA Authentication) ──
SANCTUM_STATEFUL_DOMAINS=your-domain.com

# ── AWS Bedrock (AI Engine) ──
AWS_ACCESS_KEY_ID=AKIA...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=us-east-1
BEDROCK_AWS_KEY="${AWS_ACCESS_KEY_ID}"
BEDROCK_AWS_SECRET="${AWS_SECRET_ACCESS_KEY}"
BEDROCK_REGION=us-east-1
BEDROCK_DAILY_LIMIT=50.00
BEDROCK_MONTHLY_LIMIT=500.00
BEDROCK_LOGGING_ENABLED=true

# ── Mail (optional) ──
MAIL_MAILER=ses
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="HormoneLens AI"

# ── Broadcasting ──
BROADCAST_CONNECTION=log     # or reverb/pusher for WebSocket alerts
```

### 3.3 AWS IAM Policy

The IAM user/role needs these permissions:

```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Action": [
        "bedrock:InvokeModel",
        "bedrock:InvokeModelWithResponseStream",
        "bedrock:ListFoundationModels"
      ],
      "Resource": "*"
    },
    {
      "Effect": "Allow",
      "Action": ["ses:SendEmail", "ses:SendRawEmail"],
      "Resource": "*"
    },
    {
      "Effect": "Allow",
      "Action": ["s3:PutObject", "s3:GetObject", "s3:DeleteObject"],
      "Resource": "arn:aws:s3:::your-bucket/*"
    },
    {
      "Effect": "Allow",
      "Action": ["cloudwatch:PutMetricData", "cloudwatch:GetMetricData"],
      "Resource": "*"
    }
  ]
}
```

---

## 4. Database Setup

### 4.1 Run Migrations

```bash
php artisan migrate --force
```

This runs 21 migrations creating:
- `users`, `cache`, `jobs`, `failed_jobs`, `job_batches`
- `health_profiles`, `digital_twins`, `simulations`, `simulation_results`
- `alerts`, `personal_access_tokens`
- `disease_diabetes`, `disease_pcod`, dynamic disease tables
- `rag_documents`, `rag_nodes`, `rag_pages`, `rag_query_logs`
- `ai_settings`, `food_glycemic_data`
- `super_admins`

### 4.2 Run Seeders

```bash
php artisan db:seed --force
```

Runs 8 seeders in order:
1. **AdminUserSeeder** — Creates default admin account
2. **DiseaseSeeder** — Creates Diabetes, PCOD/PCOS, Thyroid, Metabolic Syndrome
3. **DiabetesRagSeeder** — RAG knowledge base for diabetes
4. **PcodRagSeeder** — RAG knowledge base for PCOD/PCOS
5. **ThyroidRagSeeder** — RAG knowledge base for thyroid disorders
6. **LifestyleNutritionRagSeeder** — RAG knowledge for lifestyle/nutrition
7. **AdminDashboardSeeder** — Dashboard configuration
8. **FoodGlycemicSeeder** — Glycemic index/load data for foods

### 4.3 Verify Data

```bash
php artisan tinker --execute="
  echo 'Diseases: ' . \App\Models\Disease::count() . PHP_EOL;
  echo 'RAG Docs: ' . \App\Models\RagDocument::count() . PHP_EOL;
  echo 'RAG Nodes: ' . \App\Models\RagNode::count() . PHP_EOL;
  echo 'RAG Pages: ' . \App\Models\RagPage::count() . PHP_EOL;
  echo 'Foods: ' . \App\Models\FoodGlycemicData::count() . PHP_EOL;
"
```

---

## 5. Optimize for Production

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize

# Link storage
php artisan storage:link

# Set permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## 6. Web Server Configuration

### Nginx

```nginx
server {
    listen 80;
    listen 443 ssl http2;
    server_name your-domain.com;

    root /var/www/hormonelens/backend/public;
    index index.php;

    # SSL
    ssl_certificate /etc/ssl/certs/your-cert.pem;
    ssl_certificate_key /etc/ssl/private/your-key.pem;

    # Redirect HTTP → HTTPS
    if ($scheme = http) {
        return 301 https://$host$request_uri;
    }

    # Headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    # Max upload size
    client_max_body_size 10M;

    # Laravel routing
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    # Deny dotfiles
    location ~ /\.(?!well-known) {
        deny all;
    }
}
```

---

## 7. Queue Workers

### 7.1 Supervisor Configuration

Create `/etc/supervisor/conf.d/hormonelens-worker.conf`:

```ini
[program:hormonelens-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/hormonelens/backend/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/hormonelens/backend/storage/logs/worker.log
stopwaitsecs=3600
```

### 7.2 Start Workers

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start hormonelens-worker:*
```

Queue workers handle:
- Alert broadcasting (AlertCreated event)
- Async AI inference jobs
- Notification delivery

---

## 8. Scheduled Tasks

Add to crontab (`crontab -e`):

```cron
* * * * * cd /var/www/hormonelens/backend && php artisan schedule:run >> /dev/null 2>&1
```

---

## 9. Health Checks

### 9.1 Application Health

```bash
# Verify app bootstraps
php artisan route:list --json | head -5

# Test AI connection
php artisan tinker --execute="
  \$bedrock = app(\Ubxty\BedrockAi\BedrockAi::class);
  echo 'Bedrock: OK' . PHP_EOL;
"
```

### 9.2 Redis Health

```bash
redis-cli ping
# Expected: PONG
```

### 9.3 Database Health

```bash
php artisan tinker --execute="
  \DB::connection()->getPdo();
  echo 'DB: OK' . PHP_EOL;
"
```

---

## 10. Monitoring

### 10.1 Log Files

```bash
# Application logs
tail -f storage/logs/laravel.log

# Queue worker logs
tail -f storage/logs/worker.log

# Nginx access logs
tail -f /var/log/nginx/access.log
```

### 10.2 CloudWatch Integration

The Bedrock integration automatically sends metrics to CloudWatch:
- AI inference latency
- Token usage per request
- Cost tracking (daily/monthly limits)
- Error rates

---

## 11. Deployment via Cloudpanzer (Recommended)

HormoneLens is configured for one-click deployment via [Cloudpanzer](https://cloudpanzer.com/):

1. Connect your GitHub repository to Cloudpanzer
2. Select the EC2 instance type (recommended: `t3.medium` or higher)
3. Configure environment variables in the Cloudpanzer dashboard
4. Deploy — Cloudpanzer handles: server provisioning, Nginx, SSL, PHP-FPM, supervisor
5. Set up auto-deploy from `main` branch

---

## 12. Post-Deployment Checklist

- [ ] `APP_DEBUG=false` in production
- [ ] `APP_ENV=production`
- [ ] SSL certificate installed and HTTPS enforced
- [ ] Database migrations run successfully (`php artisan migrate --force`)
- [ ] Seeders run successfully (`php artisan db:seed --force`)
- [ ] Frontend assets built (`npm run build`)
- [ ] Config/route/view caches generated
- [ ] Storage directory linked (`php artisan storage:link`)
- [ ] Queue workers running via Supervisor
- [ ] Cron job configured for scheduler
- [ ] Redis connected and responding
- [ ] Bedrock AI connection verified
- [ ] Admin user can log in at `/admin/login`
- [ ] Regular user registration works at `/register`
- [ ] Simulation engine produces results
- [ ] RAG knowledge base returning answers
- [ ] Alerts system functional
- [ ] Predictions endpoint returning data
- [ ] File permissions correct (storage/ and bootstrap/cache/ writable)
- [ ] Firewall rules: 80/443 open, DB/Redis ports restricted

---

## 13. Rollback Procedure

```bash
# Rollback last migration batch
php artisan migrate:rollback --force

# Or reset to specific commit
git checkout <previous-commit-hash>
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan config:cache && php artisan route:cache && php artisan view:cache
sudo supervisorctl restart hormonelens-worker:*
```

---

## Architecture Summary

```
                    ┌─────────────┐
                    │   Nginx     │
                    │  (SSL/LB)   │
                    └──────┬──────┘
                           │
                    ┌──────▼──────┐
                    │  PHP-FPM    │
                    │  Laravel 12 │
                    └──┬───┬───┬──┘
                       │   │   │
              ┌────────┘   │   └────────┐
              │            │            │
       ┌──────▼──┐  ┌──────▼──┐  ┌──────▼──────┐
       │  MySQL  │  │  Redis  │  │  AWS Bedrock │
       │  / RDS  │  │ Cache+Q │  │  Claude 3.5  │
       └─────────┘  └─────────┘  └─────────────┘
```
