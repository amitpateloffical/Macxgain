# Production API Setup Guide

## 🚨 Current Issue
Production server (`https://GainTradeX.com`) पर backup API routes properly configured नहीं हैं। API calls HTML response return कर रहे हैं instead of JSON।

## 🔧 Solution Steps

### 1. Upload Updated Files to Production Server

Upload these files to production server:
```
- .htaccess (updated with API routing)
- app/Http/Controllers/BackupController.php
- routes/api.php
- public/build/ (entire folder with new assets)
```

### 2. Run Commands on Production Server

```bash
# Clear caches
php artisan config:clear
php artisan route:clear
php artisan cache:clear

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. Verify API Routes

```bash
# Check if backup routes are registered
php artisan route:list | grep -i backup

# Test API endpoint
curl https://GainTradeX.com/api/backups
```

### 4. Check Web Server Configuration

#### Apache (.htaccess)
Ensure `.htaccess` file has:
```apache
# API Routes - Ensure API calls go to Laravel
RewriteCond %{REQUEST_URI} ^/api/
RewriteRule ^(.*)$ index.php [QSA,L]
```

#### Nginx
Add to nginx config:
```nginx
location /api/ {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### 5. Test API Endpoints

```bash
# Test backup API
curl -X GET "https://GainTradeX.com/api/backups" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Should return JSON, not HTML
```

## 🎯 Expected Results

### ✅ Success Response:
```json
{
  "success": true,
  "backups": [],
  "stats": {
    "database": 0,
    "files": 0,
    "lastBackup": null,
    "totalSize": "0 B"
  }
}
```

### ❌ Current Error Response:
```html
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    ...
  </head>
  <body>
    <div id="app"></div>
    ...
  </body>
</html>
```

## 🔍 Troubleshooting

### If API still returns HTML:

1. **Check .htaccess file:**
   ```bash
   cat .htaccess | grep -i api
   ```

2. **Check Laravel routes:**
   ```bash
   php artisan route:list | grep backup
   ```

3. **Check web server logs:**
   ```bash
   tail -f /var/log/apache2/error.log
   # or
   tail -f /var/log/nginx/error.log
   ```

4. **Test direct PHP execution:**
   ```bash
   php artisan tinker
   >>> Route::get('/api/backups', function() { return response()->json(['test' => 'ok']); });
   ```

## 📋 Quick Fix Commands

```bash
# 1. Upload files to production
# 2. Run these commands:

cd /path/to/GainTradeX
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache

# 3. Test
curl https://GainTradeX.com/api/backups
```

## 🎉 After Fix

Once API is working, backup functionality will be available at:
- `https://GainTradeX.com/admin/backup`
- All backup operations (create, download, restore, delete) will work
- No more "API not configured" errors

## 📞 Support

If issues persist, check:
1. Web server configuration
2. Laravel route caching
3. File permissions
4. .htaccess syntax
