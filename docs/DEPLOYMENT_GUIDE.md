# E-Arsip Deployment Guide

## System Requirements

### Minimum Requirements
- **PHP**: 7.4 or higher (8.0+ recommended)
- **MySQL/MariaDB**: 5.7+ / 10.3+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Memory**: 512MB RAM minimum (1GB+ recommended)
- **Storage**: 2GB free space minimum (10GB+ recommended)
- **OS**: Linux (Ubuntu 20.04+, CentOS 8+) or Windows Server 2019+

### Recommended Production Environment
- **PHP**: 8.1+
- **MySQL**: 8.0+
- **Web Server**: Nginx 1.20+ with PHP-FPM
- **Memory**: 2GB+ RAM
- **Storage**: 20GB+ SSD
- **SSL Certificate**: Let's Encrypt or commercial SSL

## Installation Steps

### 1. Server Preparation

#### Ubuntu/Debian
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y nginx mysql-server php8.1-fpm php8.1-mysql php8.1-mbstring php8.1-xml php8.1-curl php8.1-zip php8.1-gd php8.1-intl

# Install additional tools
sudo apt install -y git unzip curl wget
```

#### CentOS/RHEL
```bash
# Update system
sudo yum update -y

# Install EPEL repository
sudo yum install -y epel-release

# Install required packages
sudo yum install -y nginx mysql-server php php-fpm php-mysql php-mbstring php-xml php-curl php-zip php-gd php-intl

# Install additional tools
sudo yum install -y git unzip curl wget
```

### 2. Database Setup

#### MySQL/MariaDB Configuration
```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Login to MySQL
mysql -u root -p

# Create database and user
CREATE DATABASE e_arsip CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'e_arsip_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON e_arsip.* TO 'e_arsip_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### Import Database Schema
```bash
# Navigate to project directory
cd /var/www/html/e-arsip

# Import database schema
mysql -u e_arsip_user -p e_arsip < database/e_arsip.sql

# Run optimization script
mysql -u e_arsip_user -p e_arsip < database/optimization.sql
```

### 3. Application Deployment

#### Clone Repository
```bash
# Navigate to web root
cd /var/www/html

# Clone repository
git clone https://github.com/tupski/E-Arsip.git e-arsip

# Set ownership
sudo chown -R www-data:www-data e-arsip
sudo chmod -R 755 e-arsip
```

#### Configure Environment
```bash
# Copy environment file
cp .env.example .env

# Edit environment configuration
nano .env
```

**.env Configuration:**
```env
# Application Settings
APP_NAME="E-Arsip"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database Configuration
DB_HOST=localhost
DB_PORT=3306
DB_NAME=e_arsip
DB_USERNAME=e_arsip_user
DB_PASSWORD=secure_password_here

# Security Settings
SESSION_LIFETIME=3600
CSRF_TOKEN_LIFETIME=3600
PASSWORD_MIN_LENGTH=8

# File Upload Settings
MAX_UPLOAD_SIZE=10485760
ALLOWED_EXTENSIONS=jpg,jpeg,png,gif,pdf,doc,docx

# Cache Settings
CACHE_ENABLED=true
CACHE_DRIVER=file
CACHE_TTL=3600

# Logging Settings
LOG_LEVEL=warning
LOG_FILE=logs/app.log
```

#### Set Permissions
```bash
# Create required directories
mkdir -p cache logs uploads/logos uploads/documents

# Set proper permissions
sudo chown -R www-data:www-data cache logs uploads
sudo chmod -R 755 cache logs uploads
sudo chmod -R 644 include/*.php
sudo chmod 600 .env
```

### 4. Web Server Configuration

#### Nginx Configuration
Create `/etc/nginx/sites-available/e-arsip`:

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com www.your-domain.com;
    
    root /var/www/html/e-arsip;
    index index.php index.html;
    
    # SSL Configuration
    ssl_certificate /path/to/ssl/certificate.crt;
    ssl_certificate_key /path/to/ssl/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512;
    ssl_prefer_server_ciphers off;
    
    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
    
    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;
    
    # Main location
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # PHP processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }
    
    # Static files caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }
    
    location ~ /(cache|logs|database|tests)/ {
        deny all;
    }
    
    location ~ \.(env|md|sql)$ {
        deny all;
    }
    
    # Upload size limit
    client_max_body_size 10M;
}
```

#### Enable Site
```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/e-arsip /etc/nginx/sites-enabled/

# Test configuration
sudo nginx -t

# Restart services
sudo systemctl restart nginx
sudo systemctl restart php8.1-fpm
```

#### Apache Configuration (Alternative)
Create `/etc/apache2/sites-available/e-arsip.conf`:

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    ServerAlias www.your-domain.com
    Redirect permanent / https://your-domain.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName your-domain.com
    ServerAlias www.your-domain.com
    DocumentRoot /var/www/html/e-arsip
    
    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /path/to/ssl/certificate.crt
    SSLCertificateKeyFile /path/to/ssl/private.key
    
    # Security Headers
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set X-Content-Type-Options "nosniff"
    
    # PHP Configuration
    <FilesMatch \.php$>
        SetHandler "proxy:unix:/var/run/php/php8.1-fpm.sock|fcgi://localhost"
    </FilesMatch>
    
    # Directory Configuration
    <Directory /var/www/html/e-arsip>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # Deny access to sensitive directories
    <DirectoryMatch "/(cache|logs|database|tests)/">
        Require all denied
    </DirectoryMatch>
    
    # Deny access to sensitive files
    <FilesMatch "\.(env|md|sql)$">
        Require all denied
    </FilesMatch>
    
    # Enable compression
    LoadModule deflate_module modules/mod_deflate.so
    <Location />
        SetOutputFilter DEFLATE
        SetEnvIfNoCase Request_URI \
            \.(?:gif|jpe?g|png)$ no-gzip dont-vary
        SetEnvIfNoCase Request_URI \
            \.(?:exe|t?gz|zip|bz2|sit|rar)$ no-gzip dont-vary
    </Location>
</VirtualHost>
```

### 5. SSL Certificate Setup

#### Using Let's Encrypt (Recommended)
```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Test auto-renewal
sudo certbot renew --dry-run
```

#### Using Commercial SSL
```bash
# Copy certificate files
sudo cp certificate.crt /etc/ssl/certs/
sudo cp private.key /etc/ssl/private/
sudo cp ca-bundle.crt /etc/ssl/certs/

# Set permissions
sudo chmod 644 /etc/ssl/certs/certificate.crt
sudo chmod 600 /etc/ssl/private/private.key
sudo chmod 644 /etc/ssl/certs/ca-bundle.crt
```

### 6. Performance Optimization

#### PHP-FPM Configuration
Edit `/etc/php/8.1/fpm/pool.d/www.conf`:

```ini
[www]
user = www-data
group = www-data
listen = /var/run/php/php8.1-fpm.sock
listen.owner = www-data
listen.group = www-data
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.process_idle_timeout = 10s
pm.max_requests = 500
```

#### PHP Configuration
Edit `/etc/php/8.1/fpm/php.ini`:

```ini
memory_limit = 256M
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
max_input_time = 300
date.timezone = Asia/Jakarta
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 4000
opcache.revalidate_freq = 2
opcache.fast_shutdown = 1
```

#### MySQL Optimization
Edit `/etc/mysql/mysql.conf.d/mysqld.cnf`:

```ini
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT
query_cache_type = 1
query_cache_size = 64M
max_connections = 200
thread_cache_size = 16
tmp_table_size = 64M
max_heap_table_size = 64M
```

### 7. Monitoring Setup

#### System Monitoring
```bash
# Install monitoring tools
sudo apt install htop iotop nethogs

# Setup log rotation
sudo nano /etc/logrotate.d/e-arsip
```

**/etc/logrotate.d/e-arsip:**
```
/var/www/html/e-arsip/logs/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
}
```

#### Health Check Script
Create `/usr/local/bin/e-arsip-health.sh`:

```bash
#!/bin/bash
DOMAIN="your-domain.com"
LOG_FILE="/var/log/e-arsip-health.log"

# Check web server
if curl -f -s "https://$DOMAIN/health" > /dev/null; then
    echo "$(date): Web server OK" >> $LOG_FILE
else
    echo "$(date): Web server DOWN" >> $LOG_FILE
    # Send alert (email, Slack, etc.)
fi

# Check database
if mysql -u e_arsip_user -p'password' -e "SELECT 1" e_arsip > /dev/null 2>&1; then
    echo "$(date): Database OK" >> $LOG_FILE
else
    echo "$(date): Database DOWN" >> $LOG_FILE
    # Send alert
fi
```

#### Cron Jobs
```bash
# Edit crontab
sudo crontab -e

# Add monitoring and maintenance tasks
*/5 * * * * /usr/local/bin/e-arsip-health.sh
0 2 * * * /var/www/html/e-arsip/scripts/cleanup-cache.php
0 3 * * 0 /var/www/html/e-arsip/scripts/optimize-database.php
```

### 8. Backup Strategy

#### Database Backup Script
Create `/usr/local/bin/backup-e-arsip.sh`:

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/e-arsip"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="e_arsip"
DB_USER="e_arsip_user"
DB_PASS="password"

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Files backup
tar -czf $BACKUP_DIR/files_$DATE.tar.gz -C /var/www/html e-arsip/uploads

# Keep only last 30 days
find $BACKUP_DIR -name "*.gz" -mtime +30 -delete

echo "Backup completed: $DATE"
```

#### Automated Backups
```bash
# Add to crontab
0 1 * * * /usr/local/bin/backup-e-arsip.sh
```

### 9. Security Hardening

#### Firewall Configuration
```bash
# Install UFW
sudo apt install ufw

# Configure firewall
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

#### Fail2Ban Setup
```bash
# Install Fail2Ban
sudo apt install fail2ban

# Configure for Nginx
sudo nano /etc/fail2ban/jail.local
```

**/etc/fail2ban/jail.local:**
```ini
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 5

[nginx-http-auth]
enabled = true
filter = nginx-http-auth
logpath = /var/log/nginx/error.log

[nginx-limit-req]
enabled = true
filter = nginx-limit-req
logpath = /var/log/nginx/error.log
maxretry = 10
```

### 10. Go Live Checklist

- [ ] Server requirements met
- [ ] Database created and imported
- [ ] Environment variables configured
- [ ] File permissions set correctly
- [ ] Web server configured and tested
- [ ] SSL certificate installed
- [ ] DNS records configured
- [ ] Monitoring setup completed
- [ ] Backup strategy implemented
- [ ] Security hardening applied
- [ ] Performance optimization done
- [ ] Health checks working
- [ ] Documentation updated
- [ ] Team training completed

### Troubleshooting

#### Common Issues

**1. Permission Denied Errors**
```bash
sudo chown -R www-data:www-data /var/www/html/e-arsip
sudo chmod -R 755 /var/www/html/e-arsip
```

**2. Database Connection Failed**
- Check database credentials in `.env`
- Verify MySQL service is running
- Test connection manually

**3. 500 Internal Server Error**
- Check PHP error logs: `/var/log/php8.1-fpm.log`
- Check Nginx error logs: `/var/log/nginx/error.log`
- Verify file permissions

**4. Upload Issues**
- Check `upload_max_filesize` in PHP configuration
- Verify upload directory permissions
- Check disk space

### Support

For deployment support:
- Email: support@your-domain.com
- Documentation: https://your-domain.com/docs
- GitHub Issues: https://github.com/tupski/E-Arsip/issues
