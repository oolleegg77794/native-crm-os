#!/bin/sh
set -e

cd /var/www/html

echo "==> Native CRM Backend Bootstrapping..."

# Ensure composer dependencies are present (handles volume mount shadowing)
if [ ! -f "/var/www/html/vendor/autoload.php" ]; then
    echo "==> Vendor dependencies not found in workspace. Running composer install..."
    composer install --prefer-dist --no-interaction --optimize-autoloader
fi

# Ensure storage directories exist and have proper permissions
mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache
chmod -R 777 storage bootstrap/cache

# Wait for PostgreSQL database
echo "==> Checking database connection on ${DB_HOST}:${DB_PORT:-5432}..."
MAX_TRIES=30
COUNT=0
until nc -z -w 2 "${DB_HOST}" "${DB_PORT:-5432}" || [ $COUNT -eq $MAX_TRIES ]; do
  echo "Waiting for postgres to become ready... ($COUNT/$MAX_TRIES)"
  sleep 1
  COUNT=$((COUNT+1))
done

if [ $COUNT -eq $MAX_TRIES ]; then
    echo "==> Warning: Could not reach Postgres after $MAX_TRIES attempts, continuing anyway..."
else
    echo "==> Database is ready!"
fi

# Wait for Redis
if [ -n "$REDIS_HOST" ]; then
  echo "==> Checking redis connection on ${REDIS_HOST}:${REDIS_PORT:-6379}..."
  COUNT=0
  until nc -z -w 2 "${REDIS_HOST}" "${REDIS_PORT:-6379}" || [ $COUNT -eq $MAX_TRIES ]; do
    echo "Waiting for redis to become ready... ($COUNT/$MAX_TRIES)"
    sleep 1
    COUNT=$((COUNT+1))
  done
  echo "==> Redis is ready!"
fi

# Generate key if not present
if [ -z "$APP_KEY" ]; then
    echo "==> Generating application key..."
    php artisan key:generate --force || true
fi

# Run Database Migrations
echo "==> Running database migrations..."
php artisan migrate --force || echo "==> Migration warning"

# Seed initial data (Admin User, Roles, Default Modules)
echo "==> Seeding initial CRM modules and admin user..."
php artisan db:seed --force || echo "==> Seeding warning"

echo "==> Starting Laravel server on 0.0.0.0:8000..."
exec php artisan serve --host=0.0.0.0 --port=8000
