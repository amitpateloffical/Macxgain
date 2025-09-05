# Macxgain Docker Setup

This document provides comprehensive instructions for running the Macxgain application using Docker.

## Prerequisites

- Docker Engine 20.10+
- Docker Compose 2.0+
- At least 4GB RAM
- At least 10GB free disk space

## Quick Start

### 🚀 One-Command Deployment

**For quick deployment, simply run:**
```bash
./quick_deploy.sh
```

This will automatically:
- Check prerequisites
- Create backup
- Pull latest changes
- Build and start all services
- Verify deployment
- Show status

### Development Environment

1. **Clone the repository and navigate to the project directory:**
   ```bash
   git clone <repository-url>
   cd Macxgain
   ```

2. **Create environment file:**
   ```bash
   cp .env.example .env
   ```

3. **Update environment variables in `.env`:**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=mysql
   DB_PORT=3306
   DB_DATABASE=macxgain
   DB_USERNAME=macxgain
   DB_PASSWORD=macxgain123
   
   REDIS_HOST=redis
   REDIS_PASSWORD=null
   REDIS_PORT=6379
   ```

4. **Start all services:**
   ```bash
   docker-compose up -d
   ```

5. **Access the application:**
   - Main Application: http://localhost
   - phpMyAdmin: http://localhost:8080
   - Database: localhost:3306

### Production Environment

1. **Create production environment file:**
   ```bash
   cp .env.example .env.production
   ```

2. **Update production environment variables:**
   ```env
   APP_ENV=production
   APP_DEBUG=false
   DB_DATABASE=your_production_db
   DB_USERNAME=your_production_user
   DB_PASSWORD=your_secure_password
   MYSQL_ROOT_PASSWORD=your_secure_root_password
   REDIS_PASSWORD=your_redis_password
   ```

3. **Start production services:**
   ```bash
   docker-compose -f docker-compose.prod.yml up -d
   ```

## Services Included

### Main Application (app)
- **Base Image:** PHP 8.2 with Apache
- **Features:**
  - Laravel 11.x framework
  - Vue.js 3.x frontend
  - Python 3.11 for TrueData WebSocket
  - Node.js 20.x for asset building
  - OPcache enabled for performance
  - Health checks included
  - **WebSocket Auto-Restart:** Services automatically restart if they crash
  - **Cache Persistence:** Laravel and Redis caches persist across restarts
  - **Background Services:** TrueData WebSocket, Scheduler, and WebSocket daemon run in background

### Database (mysql)
- **Image:** MySQL 8.0
- **Features:**
  - Persistent data storage
  - Optimized for Laravel
  - Native password authentication

### Cache (redis)
- **Image:** Redis 7 Alpine
- **Features:**
  - Session storage
  - Cache storage
  - Queue management

### Database Management (phpmyadmin)
- **Image:** phpMyAdmin
- **Features:**
  - Web-based MySQL administration
  - Available only in development

## 🚀 Deployment Commands

### Automatic Deployment
```bash
# Quick deployment (development)
./quick_deploy.sh

# Full deployment with options
./deploy.sh deploy                    # Development deployment
./deploy.sh deploy:prod              # Production deployment
./deploy.sh rollback                 # Rollback to previous version
./deploy.sh status                   # Show deployment status
./deploy.sh backup                   # Create backup only
./deploy.sh logs                     # View application logs
./deploy.sh help                     # Show help
```

### Manual Docker Commands

#### Build and Start
```bash
# Development
docker-compose up -d

# Production
docker-compose -f docker-compose.prod.yml up -d

# Build without cache
docker-compose build --no-cache
```

#### Management
```bash
# View logs
docker-compose logs -f app

# Execute commands in container
docker-compose exec app php artisan migrate
docker-compose exec app python3 truedata_websocket.py

# Stop services
docker-compose down

# Stop and remove volumes
docker-compose down -v
```

#### WebSocket Management
```bash
# Check WebSocket status
docker-compose exec app pgrep -f "truedata_websocket.py"

# Restart WebSocket services
docker-compose exec app pkill -f "truedata_websocket.py"
docker-compose exec app python3 truedata_websocket.py &

# View WebSocket logs
docker-compose exec app tail -f /var/log/truedata_websocket.log
```

### Maintenance
```bash
# Update application
docker-compose pull
docker-compose up -d

# Backup database
docker-compose exec mysql mysqldump -u root -p macxgain > backup.sql

# Restore database
docker-compose exec -T mysql mysql -u root -p macxgain < backup.sql
```

## Environment Variables

### Required Variables
- `DB_DATABASE`: Database name
- `DB_USERNAME`: Database username
- `DB_PASSWORD`: Database password
- `MYSQL_ROOT_PASSWORD`: MySQL root password

### Optional Variables
- `APP_ENV`: Application environment (production/development)
- `APP_DEBUG`: Debug mode (true/false)
- `REDIS_PASSWORD`: Redis password
- `PHP_MEMORY_LIMIT`: PHP memory limit (default: 1024M)

## Troubleshooting

### Common Issues

1. **Port conflicts:**
   ```bash
   # Check what's using port 80
   sudo lsof -i :80
   
   # Change ports in docker-compose.yml
   ports:
     - "8080:80"  # Use port 8080 instead
   ```

2. **Permission issues:**
   ```bash
   # Fix storage permissions
   docker-compose exec app chown -R www-data:www-data storage
   docker-compose exec app chmod -R 755 storage
   ```

3. **Database connection issues:**
   ```bash
   # Check database logs
   docker-compose logs mysql
   
   # Test connection
   docker-compose exec app php artisan db:show
   ```

4. **Memory issues:**
   ```bash
   # Increase Docker memory limit
   # In Docker Desktop: Settings > Resources > Memory
   ```

### Logs and Debugging

```bash
# View all logs
docker-compose logs

# View specific service logs
docker-compose logs app
docker-compose logs mysql
docker-compose logs redis

# Follow logs in real-time
docker-compose logs -f app

# Check container status
docker-compose ps

# Check resource usage
docker stats
```

## Performance Optimization

### Production Optimizations
1. **Enable OPcache** (already configured)
2. **Use Redis for sessions and cache**
3. **Optimize MySQL settings**
4. **Use CDN for static assets**
5. **Enable Gzip compression**

### Monitoring
```bash
# Check container health
docker-compose ps

# Monitor resource usage
docker stats

# Check application health
curl http://localhost/health
```

## Security Considerations

1. **Change default passwords**
2. **Use strong database passwords**
3. **Enable Redis authentication**
4. **Use HTTPS in production**
5. **Regular security updates**
6. **Backup data regularly**

## Backup and Recovery

### Database Backup
```bash
# Create backup
docker-compose exec mysql mysqldump -u root -p macxgain > backup_$(date +%Y%m%d_%H%M%S).sql

# Restore backup
docker-compose exec -T mysql mysql -u root -p macxgain < backup_file.sql
```

### Application Backup
```bash
# Backup storage directory
tar -czf storage_backup_$(date +%Y%m%d_%H%M%S).tar.gz storage/

# Backup uploaded files
tar -czf files_backup_$(date +%Y%m%d_%H%M%S).tar.gz public/files/
```

## Support

For issues and questions:
1. Check the logs first
2. Review this documentation
3. Check Docker and Docker Compose versions
4. Verify environment variables
5. Contact the development team

## Version Information

- **PHP:** 8.2
- **Laravel:** 11.x
- **Vue.js:** 3.x
- **Node.js:** 20.x
- **Python:** 3.11
- **MySQL:** 8.0
- **Redis:** 7.x
