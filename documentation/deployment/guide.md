# Deployment Guide

This guide covers deploying the Olympic Application API to production environments.

## Prerequisites

### System Requirements
- **Operating System**: Ubuntu 20.04 LTS or later
- **PHP**: 8.1 or later
- **Database**: PostgreSQL 13+ or MySQL 8+
- **Web Server**: Nginx or Apache
- **Process Manager**: Supervisor
- **Cache**: Redis (optional but recommended)
- **Memory**: Minimum 2GB RAM
- **Storage**: Minimum 20GB SSD

### Required Software
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP and extensions
sudo apt install php8.1-fpm php8.1-cli php8.1-common php8.1-mysql php8.1-pgsql php8.1-xml php8.1-xmlrpc php8.1-curl php8.1-gd php8.1-imagick php8.1-cli php8.1-dev php8.1-imap php8.1-mbstring php8.1-opcache php8.1-soap php8.1-zip php8.1-intl php8.1-bcmath -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js and npm
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Install PostgreSQL
sudo apt install postgresql postgresql-contrib -y

# Install Redis
sudo apt install redis-server -y

# Install Nginx
sudo apt install nginx -y

# Install Supervisor
sudo apt install supervisor -y
```

## Database Setup

### PostgreSQL Configuration
```bash
# Switch to postgres user
sudo -u postgres psql

# Create database and user
CREATE DATABASE olimpiapp_db;
CREATE USER olimpiapp_user WITH PASSWORD 'secure_password_here';
GRANT ALL PRIVILEGES ON DATABASE olimpiapp_db TO olimpiapp_user;
\q
```

### Database Security
```bash
# Edit PostgreSQL configuration
sudo nano /etc/postgresql/13/main/postgresql.conf

# Update these settings:
listen_addresses = 'localhost'
max_connections = 100
shared_buffers = 256MB
effective_cache_size = 1GB

# Edit pg_hba.conf for authentication
sudo nano /etc/postgresql/13/main/pg_hba.conf

# Add this line for application access:
local   olimpiapp_db    olimpiapp_user    md5

# Restart PostgreSQL
sudo systemctl restart postgresql
```

## Application Deployment

### 1. Clone Repository
```bash
# Create application directory
sudo mkdir -p /var/www/olimpiapp-api
cd /var/www/olimpiapp-api

# Clone from Git (replace with your repository URL)
git clone https://github.com/your-username/olimpiapp-api.git .

# Set proper ownership
sudo chown -R www-data:www-data /var/www/olimpiapp-api
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node.js dependencies
npm install

# Build frontend assets
npm run build
```

### 3. Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Edit environment variables
nano .env
```

**Production .env Configuration:**
```env
APP_NAME="Olympic Application API"
APP_ENV=production
APP_KEY=base64:GENERATE_NEW_KEY_HERE
APP_DEBUG=false
APP_URL=https://api.yourdomain.com

LOG_CHANNEL=single
LOG_LEVEL=error
LOG_SLACK_WEBHOOK_URL=

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=olimpiapp_db
DB_USERNAME=olimpiapp_user
DB_PASSWORD=secure_password_here

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"

SANCTUM_STATEFUL_DOMAINS=yourdomain.com,www.yourdomain.com
SESSION_DOMAIN=.yourdomain.com

TELESCOPE_ENABLED=false
```

### 4. Application Setup
```bash
# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate --force

# Seed database (if needed)
php artisan db:seed --force

# Clear and cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Create storage links
php artisan storage:link

# Set permissions
sudo chown -R www-data:www-data /var/www/olimpiapp-api
sudo chmod -R 755 /var/www/olimpiapp-api
sudo chmod -R 775 /var/www/olimpiapp-api/storage
sudo chmod -R 775 /var/www/olimpiapp-api/bootstrap/cache
```

## Web Server Configuration

### Nginx Configuration
```bash
# Create Nginx configuration
sudo nano /etc/nginx/sites-available/olimpiapp-api
```

**Nginx Configuration File:**
```nginx
server {
    listen 80;
    server_name api.yourdomain.com;
    root /var/www/olimpiapp-api/public;
    index index.php;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Rate limiting
    limit_req_zone $binary_remote_addr zone=api:10m rate=10r/m;
    limit_req zone=api burst=20 nodelay;

    # Main location
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM configuration
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Security: Block access to sensitive files
    location ~ /\. {
        deny all;
    }
    
    location ~* \.(log|env|git|config)$ {
        deny all;
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;

    # Client max body size
    client_max_body_size 10M;

    # Logging
    access_log /var/log/nginx/olimpiapp-api.access.log;
    error_log /var/log/nginx/olimpiapp-api.error.log;
}
```

### Enable Site
```bash
# Enable the site
sudo ln -s /etc/nginx/sites-available/olimpiapp-api /etc/nginx/sites-enabled/

# Remove default site
sudo rm /etc/nginx/sites-enabled/default

# Test configuration
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx
```

## SSL/TLS Configuration

### Using Let's Encrypt
```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Obtain SSL certificate
sudo certbot --nginx -d api.yourdomain.com

# Test auto-renewal
sudo certbot renew --dry-run
```

### SSL Nginx Configuration Update
After obtaining SSL, your Nginx config will be updated. Add these additional security headers:

```nginx
# Add to server block
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';" always;

# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name api.yourdomain.com;
    return 301 https://$server_name$request_uri;
}
```

## Queue Workers

### Supervisor Configuration
```bash
# Create supervisor configuration
sudo nano /etc/supervisor/conf.d/olimpiapp-worker.conf
```

**Supervisor Configuration:**
```ini
[program:olimpiapp-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/olimpiapp-api/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/log/supervisor/olimpiapp-worker.log
stopwaitsecs=3600
```

### Start Workers
```bash
# Update supervisor configuration
sudo supervisorctl reread
sudo supervisorctl update

# Start workers
sudo supervisorctl start olimpiapp-worker:*

# Check status
sudo supervisorctl status
```

## Monitoring and Logging

### Log Rotation
```bash
# Create logrotate configuration
sudo nano /etc/logrotate.d/olimpiapp-api
```

**Logrotate Configuration:**
```
/var/www/olimpiapp-api/storage/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    postrotate
        /bin/systemctl reload php8.1-fpm
    endscript
}
```

### System Monitoring
```bash
# Install monitoring tools
sudo apt install htop iotop nethogs -y

# Monitor processes
htop

# Monitor disk I/O
sudo iotop

# Monitor network
sudo nethogs
```

## Backup Strategy

### Database Backup Script
```bash
# Create backup script
sudo nano /usr/local/bin/backup-olimpiapp.sh
```

**Backup Script:**
```bash
#!/bin/bash
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="/var/backups/olimpiapp"
DB_NAME="olimpiapp_db"
DB_USER="olimpiapp_user"
APP_DIR="/var/www/olimpiapp-api"

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
pg_dump -U $DB_USER -h localhost $DB_NAME > $BACKUP_DIR/db_backup_$TIMESTAMP.sql

# Application backup
tar -czf $BACKUP_DIR/app_backup_$TIMESTAMP.tar.gz -C $APP_DIR .

# Storage backup
tar -czf $BACKUP_DIR/storage_backup_$TIMESTAMP.tar.gz -C $APP_DIR/storage .

# Remove old backups (keep last 7 days)
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete

echo "Backup completed: $TIMESTAMP"
```

### Automated Backups
```bash
# Make script executable
sudo chmod +x /usr/local/bin/backup-olimpiapp.sh

# Add to crontab
sudo crontab -e

# Add this line for daily backups at 2 AM
0 2 * * * /usr/local/bin/backup-olimpiapp.sh
```

## Performance Optimization

### PHP-FPM Configuration
```bash
# Edit PHP-FPM configuration
sudo nano /etc/php/8.1/fpm/pool.d/www.conf
```

**Key Settings:**
```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 1000
```

### Redis Configuration
```bash
# Edit Redis configuration
sudo nano /etc/redis/redis.conf
```

**Key Settings:**
```
maxmemory 256mb
maxmemory-policy allkeys-lru
save 900 1
save 300 10
save 60 10000
```

### Database Optimization
```sql
-- Connect to database
sudo -u postgres psql olimpiapp_db

-- Analyze tables
ANALYZE;

-- Update statistics
UPDATE pg_stat_user_tables SET n_tup_ins = 0, n_tup_upd = 0, n_tup_del = 0;
```

## Security Hardening

### Firewall Configuration
```bash
# Enable UFW
sudo ufw enable

# Allow SSH
sudo ufw allow ssh

# Allow HTTP/HTTPS
sudo ufw allow 80
sudo ufw allow 443

# Allow PostgreSQL (only from localhost)
sudo ufw allow from 127.0.0.1 to any port 5432

# Check status
sudo ufw status
```

### Fail2ban Configuration
```bash
# Install Fail2ban
sudo apt install fail2ban -y

# Create custom jail
sudo nano /etc/fail2ban/jail.local
```

**Fail2ban Configuration:**
```ini
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 3

[sshd]
enabled = true

[nginx-http-auth]
enabled = true

[nginx-limit-req]
enabled = true
filter = nginx-limit-req
action = iptables-multiport[name=ReqLimit, port="http,https", protocol=tcp]
logpath = /var/log/nginx/olimpiapp-api.error.log
maxretry = 5
```

## Health Checks

### Application Health Check
```bash
# Create health check script
sudo nano /usr/local/bin/health-check.sh
```

**Health Check Script:**
```bash
#!/bin/bash
API_URL="https://api.yourdomain.com/api/health"
RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" $API_URL)

if [ $RESPONSE -eq 200 ]; then
    echo "$(date): API is healthy"
else
    echo "$(date): API is unhealthy (HTTP $RESPONSE)"
    # Send alert (email, Slack, etc.)
fi
```

### Monitoring Cron
```bash
# Add to crontab for monitoring every 5 minutes
*/5 * * * * /usr/local/bin/health-check.sh >> /var/log/health-check.log
```

## Deployment Checklist

### Pre-Deployment
- [ ] Database backup completed
- [ ] Dependencies updated
- [ ] Environment variables configured
- [ ] SSL certificate valid
- [ ] Firewall rules configured
- [ ] Monitoring setup

### During Deployment
- [ ] Put application in maintenance mode
- [ ] Pull latest code
- [ ] Install/update dependencies
- [ ] Run database migrations
- [ ] Clear and rebuild caches
- [ ] Restart services
- [ ] Remove from maintenance mode

### Post-Deployment
- [ ] Health checks passing
- [ ] Logs monitoring
- [ ] Performance metrics normal
- [ ] Backup verification
- [ ] Queue workers running
- [ ] SSL certificate working

## Troubleshooting

### Common Issues

**Permission Errors:**
```bash
sudo chown -R www-data:www-data /var/www/olimpiapp-api
sudo chmod -R 755 /var/www/olimpiapp-api
sudo chmod -R 775 /var/www/olimpiapp-api/storage
sudo chmod -R 775 /var/www/olimpiapp-api/bootstrap/cache
```

**Database Connection Issues:**
```bash
# Check PostgreSQL status
sudo systemctl status postgresql

# Check database connectivity
php artisan tinker
DB::connection()->getPdo();
```

**Queue Worker Issues:**
```bash
# Check supervisor status
sudo supervisorctl status

# Restart workers
sudo supervisorctl restart olimpiapp-worker:*

# Check logs
sudo tail -f /var/log/supervisor/olimpiapp-worker.log
```

### Log Locations
- **Application logs**: `/var/www/olimpiapp-api/storage/logs/`
- **Nginx logs**: `/var/log/nginx/`
- **PHP-FPM logs**: `/var/log/php8.1-fpm.log`
- **Supervisor logs**: `/var/log/supervisor/`
- **System logs**: `/var/log/syslog`

---

*For additional deployment configurations and advanced setups, consult the specific documentation for your hosting environment.*
