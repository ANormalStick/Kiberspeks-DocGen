#!/bin/sh
set -e

# Ensure writable (in case of mounted disks)
chmod -R 775 storage bootstrap/cache || true

# Clear any stale caches (if image was rebuilt)
php artisan cache:clear || true
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Storage symlink (idempotent)
php artisan storage:link --force || true

# Now that Render has injected env vars (APP_KEY, etc.), cache config/routes
php artisan config:cache || true
php artisan route:cache || true

# Create sqlite file if missing
mkdir -p /app/database
[ -f /app/database/database.sqlite ] || touch /app/database/database.sqlite

# Create sessions table if using database driver
if [ "${SESSION_DRIVER}" = "database" ]; then
  php artisan session:table || true
  php artisan migrate --force || true
fi


# Start Laravel dev server (demo-friendly)
php -S 0.0.0.0:${PORT} -t public
