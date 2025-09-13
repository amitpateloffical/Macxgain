#!/bin/bash

# Macxgain Auto Deploy Script
# This script handles automatic deployment with zero downtime

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
PROJECT_NAME="macxgain"
DOCKER_COMPOSE_FILE="docker-compose.yml"
DOCKER_COMPOSE_PROD_FILE="docker-compose.prod.yml"
BACKUP_DIR="./backups"
LOG_FILE="./deploy.log"

# Function to log with timestamp
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

# Function to print colored output
print_status() {
    local color=$1
    local message=$2
    echo -e "${color}${message}${NC}"
    log "$message"
}

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Function to check prerequisites
check_prerequisites() {
    print_status $BLUE "🔍 Checking prerequisites..."
    
    if ! command_exists docker; then
        print_status $RED "❌ Docker is not installed!"
        exit 1
    fi
    
    if ! command_exists docker-compose; then
        print_status $RED "❌ Docker Compose is not installed!"
        exit 1
    fi
    
    if ! command_exists git; then
        print_status $RED "❌ Git is not installed!"
        exit 1
    fi
    
    print_status $GREEN "✅ All prerequisites are met"
}

# Function to create backup
create_backup() {
    print_status $BLUE "💾 Creating backup..."
    
    local backup_name="backup_$(date +%Y%m%d_%H%M%S)"
    local backup_path="$BACKUP_DIR/$backup_name"
    
    mkdir -p "$backup_path"
    
    # Backup database (host MySQL)
    if systemctl is-active --quiet mysql; then
        print_status $YELLOW "📊 Backing up host MySQL database..."
        mysqldump -h localhost -u root -p"Kabirisgod@7354$" macxgain > "$backup_path/database.sql"
    else
        print_status $YELLOW "⚠️  Host MySQL not running, skipping database backup"
    fi
    
    # Backup storage
    if [ -d "./storage" ]; then
        print_status $YELLOW "📁 Backing up storage..."
        cp -r ./storage "$backup_path/"
    fi
    
    # Backup uploaded files
    if [ -d "./public/files" ]; then
        print_status $YELLOW "📄 Backing up uploaded files..."
        cp -r ./public/files "$backup_path/"
    fi
    
    # Backup user media from host volume
    if [ -d "/var/macxgain-data" ]; then
        print_status $YELLOW "📸 Backing up user media and trading data..."
        cp -r /var/macxgain-data "$backup_path/"
    fi
    
    # Backup environment file
    if [ -f ".env" ]; then
        cp .env "$backup_path/"
    fi
    
    print_status $GREEN "✅ Backup created: $backup_path"
    echo "$backup_path" > .last_backup
}

# Function to pull latest changes
pull_changes() {
    print_status $BLUE "📥 Pulling latest changes..."
    
    # Check if we're in a git repository
    if [ ! -d ".git" ]; then
        print_status $YELLOW "⚠️  Not a git repository, skipping pull"
        return
    fi
    
    # Stash any local changes
    if ! git diff --quiet; then
        print_status $YELLOW "💾 Stashing local changes..."
        git stash push -m "Auto-stash before deploy $(date)"
    fi
    
    # Pull latest changes
    git pull origin main
    
    print_status $GREEN "✅ Latest changes pulled"
}

# Function to build Docker images
build_images() {
    print_status $BLUE "🔨 Building Docker images..."
    
    # Build with no cache for production
    if [ "$1" = "production" ]; then
        docker-compose -f "$DOCKER_COMPOSE_PROD_FILE" build --no-cache
    else
        docker-compose build
    fi
    
    print_status $GREEN "✅ Docker images built"
}

# Function to run migrations
run_migrations() {
    print_status $BLUE "🗄️  Running database migrations..."
    
    # Wait for database to be ready (host MySQL)
    local max_attempts=30
    local attempt=1
    
    while [ $attempt -le $max_attempts ]; do
        if docker-compose exec -T app php artisan db:show --quiet 2>/dev/null; then
            break
        fi
        
        print_status $YELLOW "⏳ Waiting for host MySQL database... (attempt $attempt/$max_attempts)"
        sleep 2
        attempt=$((attempt + 1))
    done
    
    if [ $attempt -gt $max_attempts ]; then
        print_status $RED "❌ Host MySQL connection failed after $max_attempts attempts"
        print_status $YELLOW "💡 Make sure MySQL is running on host: sudo systemctl start mysql"
        exit 1
    fi
    
    # Run migrations
    docker-compose exec -T app php artisan migrate --force
    
    print_status $GREEN "✅ Database migrations completed"
}

# Function to clear and cache Laravel
optimize_laravel() {
    print_status $BLUE "⚡ Optimizing Laravel..."
    
    # Clear caches
    docker-compose exec -T app php artisan config:clear
    docker-compose exec -T app php artisan route:clear
    docker-compose exec -T app php artisan view:clear
    docker-compose exec -T app php artisan event:clear
    
    # Cache configurations
    docker-compose exec -T app php artisan config:cache
    docker-compose exec -T app php artisan route:cache
    docker-compose exec -T app php artisan view:cache
    docker-compose exec -T app php artisan event:cache
    
    # Optimize autoloader
    docker-compose exec -T app php artisan optimize
    
    print_status $GREEN "✅ Laravel optimized"
}

# Function to restart services
restart_services() {
    print_status $BLUE "🔄 Restarting services..."
    
    # Restart all services
    docker-compose down
    sleep 5
    docker-compose up -d
    
    # Wait for services to be healthy
    local max_attempts=30
    local attempt=1
    
    while [ $attempt -le $max_attempts ]; do
        if docker-compose ps | grep -q "Up (healthy)"; then
            break
        fi
        
        print_status $YELLOW "⏳ Waiting for services to be healthy... (attempt $attempt/$max_attempts)"
        sleep 2
        attempt=$((attempt + 1))
    done
    
    if [ $attempt -gt $max_attempts ]; then
        print_status $RED "❌ Services failed to become healthy"
        docker-compose logs
        exit 1
    fi
    
    print_status $GREEN "✅ Services restarted and healthy"
}

# Function to verify deployment
verify_deployment() {
    print_status $BLUE "🔍 Verifying deployment..."
    
    # Check if application is responding
    local max_attempts=10
    local attempt=1
    
    while [ $attempt -le $max_attempts ]; do
        if curl -f http://localhost/ >/dev/null 2>&1; then
            break
        fi
        
        print_status $YELLOW "⏳ Waiting for application to respond... (attempt $attempt/$max_attempts)"
        sleep 3
        attempt=$((attempt + 1))
    done
    
    if [ $attempt -gt $max_attempts ]; then
        print_status $RED "❌ Application is not responding"
        docker-compose logs app
        exit 1
    fi
    
    # Check WebSocket services
    if docker-compose exec -T app pgrep -f "truedata_websocket.py" >/dev/null; then
        print_status $GREEN "✅ TrueData WebSocket is running"
    else
        print_status $YELLOW "⚠️  TrueData WebSocket is not running"
    fi
    
    if docker-compose exec -T app pgrep -f "start_scheduler.sh" >/dev/null; then
        print_status $GREEN "✅ Scheduler is running"
    else
        print_status $YELLOW "⚠️  Scheduler is not running"
    fi
    
    print_status $GREEN "✅ Deployment verification completed"
}

# Function to cleanup old backups
cleanup_backups() {
    print_status $BLUE "🧹 Cleaning up old backups..."
    
    # Keep only last 5 backups
    if [ -d "$BACKUP_DIR" ]; then
        cd "$BACKUP_DIR"
        ls -t | tail -n +6 | xargs -r rm -rf
        cd - >/dev/null
    fi
    
    print_status $GREEN "✅ Old backups cleaned up"
}

# Function to show deployment status
show_status() {
    print_status $BLUE "📊 Deployment Status:"
    
    echo ""
    echo "🐳 Docker Containers:"
    docker-compose ps
    
    echo ""
    echo "💾 Disk Usage:"
    df -h | grep -E "(Filesystem|/dev/)"
    
    echo ""
    echo "🧠 Memory Usage:"
    free -h
    
    echo ""
    echo "📈 Application Health:"
    if curl -f http://localhost/ >/dev/null 2>&1; then
        print_status $GREEN "✅ Application is healthy"
    else
        print_status $RED "❌ Application is not responding"
    fi
}

# Function to rollback
rollback() {
    print_status $BLUE "🔄 Rolling back to previous version..."
    
    if [ ! -f ".last_backup" ]; then
        print_status $RED "❌ No backup found for rollback"
        exit 1
    fi
    
    local backup_path=$(cat .last_backup)
    
    if [ ! -d "$backup_path" ]; then
        print_status $RED "❌ Backup directory not found: $backup_path"
        exit 1
    fi
    
    # Stop current services
    docker-compose down
    
    # Restore database (host MySQL)
    if [ -f "$backup_path/database.sql" ]; then
        print_status $YELLOW "📊 Restoring host MySQL database..."
        mysql -h localhost -u root -p"Kabirisgod@7354$" macxgain < "$backup_path/database.sql"
    fi
    
    # Restore storage
    if [ -d "$backup_path/storage" ]; then
        print_status $YELLOW "📁 Restoring storage..."
        rm -rf ./storage
        cp -r "$backup_path/storage" ./
    fi
    
    # Restore uploaded files
    if [ -d "$backup_path/files" ]; then
        print_status $YELLOW "📄 Restoring uploaded files..."
        rm -rf ./public/files
        cp -r "$backup_path/files" ./public/
    fi
    
    # Restore user media to host volume
    if [ -d "$backup_path/macxgain-data" ]; then
        print_status $YELLOW "📸 Restoring user media and trading data..."
        sudo rm -rf /var/macxgain-data
        sudo cp -r "$backup_path/macxgain-data" /var/
        sudo chown -R 33:33 /var/macxgain-data
    fi
    
    # Restore environment
    if [ -f "$backup_path/.env" ]; then
        cp "$backup_path/.env" ./
    fi
    
    # Restart services
    docker-compose up -d
    
    print_status $GREEN "✅ Rollback completed"
}

# Main deployment function
deploy() {
    local environment=${1:-development}
    
    print_status $GREEN "🚀 Starting deployment for $environment environment..."
    
    # Check prerequisites
    check_prerequisites
    
    # Create backup
    create_backup
    
    # Pull latest changes
    pull_changes
    
    # Build images
    build_images "$environment"
    
    # Run migrations
    run_migrations
    
    # Optimize Laravel
    optimize_laravel
    
    # Restart services
    restart_services
    
    # Verify deployment
    verify_deployment
    
    # Cleanup old backups
    cleanup_backups
    
    print_status $GREEN "🎉 Deployment completed successfully!"
    
    # Show status
    show_status
}

# Main script logic
case "${1:-deploy}" in
    "deploy")
        deploy "${2:-development}"
        ;;
    "deploy:prod")
        deploy "production"
        ;;
    "rollback")
        rollback
        ;;
    "status")
        show_status
        ;;
    "backup")
        create_backup
        ;;
    "logs")
        docker-compose logs -f
        ;;
    "help"|"-h"|"--help")
        echo "Usage: $0 [command] [options]"
        echo ""
        echo "Commands:"
        echo "  deploy [environment]  Deploy application (default: development)"
        echo "  deploy:prod          Deploy to production"
        echo "  rollback             Rollback to previous version"
        echo "  status               Show deployment status"
        echo "  backup               Create backup only"
        echo "  logs                 Show application logs"
        echo "  help                 Show this help message"
        echo ""
        echo "Examples:"
        echo "  $0 deploy            # Deploy to development"
        echo "  $0 deploy:prod       # Deploy to production"
        echo "  $0 rollback          # Rollback to previous version"
        echo "  $0 status            # Show current status"
        ;;
    *)
        print_status $RED "❌ Unknown command: $1"
        echo "Use '$0 help' for usage information"
        exit 1
        ;;
esac
