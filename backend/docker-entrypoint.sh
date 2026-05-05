#!/bin/sh
set -e

echo "=== Starting HDBarber Container ==="

# Generar APP_KEY si no existe o es placeholder
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:placeholderkeymustbereplaced" ]; then
    echo "Generating APP_KEY..."
    # Generar 32 bytes aleatorios y codificar en base64 (formato Laravel)
    APP_KEY="base64:$(openssl rand -base64 32)"
    echo "APP_KEY generated"
fi

# Crear .env desde variables de entorno de Render
cat > /var/www/html/.env << EOF
APP_NAME=HDBarber
APP_ENV=production
APP_KEY=${APP_KEY}
APP_DEBUG=${APP_DEBUG:-false}
APP_URL=${APP_URL:-https://peluqueria-hdbarber.onrender.com}
APP_TIMEZONE=Europe/Madrid

LOG_CHANNEL=stderr
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=${DB_HOST:-}
DB_PORT=${DB_PORT:-3306}
DB_DATABASE=${DB_DATABASE:-}
DB_USERNAME=${DB_USERNAME:-}
DB_PASSWORD=${DB_PASSWORD:-}

BROADCAST_DRIVER=log
CACHE_STORE=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=cookie
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

SANCTUM_STATEFUL_DOMAINS=${SANCTUM_STATEFUL_DOMAINS:-peluqueria-hdbarber.vercel.app,*.vercel.app,peluqueria-hdbarber.onrender.com}
SPA_URL=${SPA_URL:-https://hdbarber.vercel.app}

CORS_ALLOWED_ORIGINS=${CORS_ALLOWED_ORIGINS:-https://peluqueria-hdbarber.vercel.app,https://*.vercel.app}

MAIL_MAILER=log

STRIPE_KEY=${STRIPE_KEY:-}
STRIPE_SECRET=${STRIPE_SECRET:-}
STRIPE_WEBHOOK_SECRET=${STRIPE_WEBHOOK_SECRET:-}
STRIPE_CURRENCY=eur

THROTTLE_LOGIN_MAX=5
THROTTLE_LOGIN_DECAY=1
EOF

echo ".env created"

# Crear directorios si no existen
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/bootstrap/cache

# Ajustar permisos
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "Permissions set"

# Ejecutar migraciones si hay base de datos configurada
cd /var/www/html
if [ -n "$DB_HOST" ]; then
    echo "Running migrations..."
    php artisan migrate --force --no-interaction 2>&1 || echo "Migration warning (DB may not be ready)"
    
    echo "Seeding database..."
    php artisan db:seed --force --no-interaction 2>&1 || true
fi

# Cachear config y rutas para producción
echo "Caching config..."
php artisan config:cache --no-interaction 2>&1 || true
php artisan route:cache --no-interaction 2>&1 || true

echo "=== Starting services ==="

# Iniciar supervisord
exec supervisord -c /etc/supervisord.conf
