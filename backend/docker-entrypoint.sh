#!/bin/sh
set -e

# Crear .env desde variables de entorno de Render
cat > /var/www/html/.env << EOF
APP_NAME=HDBarber
APP_ENV=production
APP_KEY=${APP_KEY:-base64:placeholderkeymustbereplaced}
APP_DEBUG=false
APP_URL=${APP_URL:-https://peluqueria-hdbarber.onrender.com}
APP_TIMEZONE=Europe/Madrid

LOG_CHANNEL=stderr
LOG_LEVEL=info

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

SANCTUM_STATEFUL_DOMAINS=${SANCTUM_STATEFUL_DOMAINS:-*.onrender.com}
SPA_URL=${SPA_URL:-https://hdbarber.vercel.app}

CORS_ALLOWED_ORIGINS=${CORS_ALLOWED_ORIGINS:-*}

MAIL_MAILER=log

STRIPE_KEY=${STRIPE_KEY:-}
STRIPE_SECRET=${STRIPE_SECRET:-}
STRIPE_WEBHOOK_SECRET=${STRIPE_WEBHOOK_SECRET:-}
STRIPE_CURRENCY=eur

THROTTLE_LOGIN_MAX=5
THROTTLE_LOGIN_DECAY=1
EOF

# Ajustar permisos
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# Iniciar supervisord
exec supervisord -c /etc/supervisord.conf
