# 🚀 Macxgain Production Deployment Guide

## 🏗️ Architecture Overview

**Production-Ready Setup:**
- ✅ **External MySQL** (Host machine) - Data persists even if containers are rebuilt
- ✅ **Nginx + PHP-FPM** - High performance web server setup
- ✅ **Persistent Media Storage** - User uploads/screenshots/trading data saved on host
- ✅ **Supervisor Process Management** - Auto-restart services
- ✅ **Docker Containers** - Only for application, not data

## 📦 What's Included

### Services:
1. **Nginx** - Web server (Port 80)
2. **PHP-FPM** - PHP processor 
3. **Laravel Queue** - Background job processing
4. **Laravel Scheduler** - Cron job management
5. **TrueData WebSocket** - Live market data
6. **Redis** - Cache and sessions
7. **PHPMyAdmin** - Database management (Port 8080)

### External Dependencies:
- **MySQL Server** - On host machine
- **User Media Storage** - Host filesystem (`/var/macxgain-data/`)

## 🚀 Quick Setup (New Server)

### Step 1: Host Setup
```bash
# Run the automated host setup
chmod +x quick_host_setup.sh
./quick_host_setup.sh
```

This will:
- Install MySQL on host machine
- Create `macxgain` database and user
- Setup media directories
- Configure MySQL for Docker access

### Step 2: Deploy Application
```bash
# Deploy with Docker
sh quick_deploy.sh
```

## 🔧 Manual Setup (If Needed)

### 1. Install Host MySQL
```bash
chmod +x setup_host_mysql.sh
./setup_host_mysql.sh
```

### 2. Deploy Application
```bash
docker-compose up --build -d
```

## 📁 Directory Structure

```
/var/macxgain-data/           # Host media storage
├── trading/                  # Trading screenshots
├── uploads/                  # User file uploads  
├── profiles/                 # Profile photos
├── screenshots/              # Trading screenshots
└── documents/                # Document uploads

/path/to/project/
├── docker/                   # Docker configurations
│   ├── nginx.conf           # Nginx configuration
│   ├── php-fpm.conf         # PHP-FPM settings
│   └── supervisord.conf     # Process management
├── setup_host_mysql.sh      # MySQL setup script
├── quick_host_setup.sh      # Quick setup script
└── docker-compose.yml       # Container orchestration
```

## 🗄️ Database Configuration

**Host MySQL Connection:**
- **Host:** `localhost` (from host) / `host.docker.internal` (from container)
- **Database:** `macxgain`
- **Username:** `macxgain`
- **Password:** `macxgain123`
- **Port:** `3306`

## 🌐 Access URLs

- **Application:** `http://your-server-ip/`
- **Database Management:** `http://your-server-ip:8080`
- **API Endpoint:** `http://your-server-ip/api/`

## 📊 Management Commands

### Deploy & Update
```bash
# Quick deployment
sh quick_deploy.sh

# Production deployment
./deploy.sh deploy:prod

# Check status
./deploy.sh status

# View logs
./deploy.sh logs
```

### Database Operations
```bash
# Run migrations
docker-compose exec app php artisan migrate

# Backup database manually
mysqldump -h localhost -u macxgain -pmacxgain123 macxgain > backup.sql

# Restore database
mysql -h localhost -u macxgain -pmacxgain123 macxgain < backup.sql
```

### Service Management
```bash
# Restart application
docker-compose restart app

# View application logs
docker-compose logs -f app

# Access container shell
docker-compose exec app bash
```

## 🔍 Monitoring

### Check Services
```bash
# Container status
docker-compose ps

# Host MySQL status  
systemctl status mysql

# Process monitoring
docker-compose exec app supervisorctl status
```

### Log Files
```bash
# Application logs
docker-compose logs app

# WebSocket logs
docker-compose exec app tail -f /var/log/supervisor/websocket.log

# Nginx logs
docker-compose exec app tail -f /var/log/nginx/access.log
```

## 💾 Backup Strategy

**Automated Backups (via deploy.sh):**
- Database backup from host MySQL
- Application storage backup  
- User media backup from `/var/macxgain-data/`
- Environment configuration backup

**Manual Backup:**
```bash
# Create backup
./deploy.sh backup

# Restore from backup
./deploy.sh rollback
```

## 🔧 Troubleshooting

### Common Issues

#### Database Connection Failed
```bash
# Check MySQL is running
systemctl status mysql

# Check MySQL configuration
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
# Ensure: bind-address = 0.0.0.0

# Restart MySQL
sudo systemctl restart mysql
```

#### Container Build Failed  
```bash
# Clear Docker cache
docker system prune -a

# Rebuild from scratch
docker-compose down
docker-compose up --build
```

#### Media Files Not Saving
```bash
# Check media directory permissions
ls -la /var/macxgain-data/
sudo chown -R 33:33 /var/macxgain-data/
sudo chmod -R 755 /var/macxgain-data/
```

#### WebSocket Not Working
```bash
# Check WebSocket process
docker-compose exec app supervisorctl status truedata-websocket

# Restart WebSocket
docker-compose exec app supervisorctl restart truedata-websocket

# View WebSocket logs
docker-compose exec app supervisorctl tail -f truedata-websocket
```

## ⚡ Performance Optimization

### Nginx Optimizations
- Gzip compression enabled
- Static file caching (1 year)
- Security headers configured
- File upload size: 100MB

### PHP-FPM Settings
- Memory limit: 1024M
- Max execution time: 300s
- Process manager: dynamic (5-50 workers)

### MySQL Optimization
- External MySQL allows for dedicated resources
- Can be tuned independently of application
- Better backup and restore capabilities

## 🛡️ Security Features

- Hide server tokens
- Security headers (XSS, CSRF, etc.)
- File access restrictions
- Environment-based configuration
- User data isolation

## 📈 Benefits of This Setup

✅ **Data Persistence** - MySQL and user uploads survive container rebuilds  
✅ **High Performance** - Nginx + PHP-FPM + external MySQL  
✅ **Easy Scaling** - Can scale database and application independently  
✅ **Better Backups** - Host-based backups are faster and more reliable  
✅ **Development Friendly** - Easy to debug and modify  
✅ **Production Ready** - Supervisor manages all processes automatically  

## 🆘 Support

If you encounter issues:
1. Check logs: `./deploy.sh logs`
2. Check status: `./deploy.sh status`  
3. Try rollback: `./deploy.sh rollback`
4. Check this documentation for troubleshooting steps

---
**🎉 Your Macxgain trading platform is now ready for production with persistent data and high performance!**
