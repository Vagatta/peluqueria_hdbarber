# HDBarber

Aplicación completa de gestión para peluquería: **API REST en Laravel 11 + SPA Vue 3 (PWA instalable)** con reservas, pagos Stripe y panel de administración.

## Stack

- **Frontend**: Vue 3 (Composition API + `<script setup>`), Pinia, Vue Router, Tailwind CSS, Vite, `vite-plugin-pwa`, axios.
- **Backend**: PHP 8.2+, Laravel 11, Eloquent, Sanctum (SPA cookies), Stripe PHP SDK.
- **DB**: MySQL / MariaDB.
- **Pagos**: Stripe Checkout + webhooks (`payment_intent.succeeded`, `checkout.session.completed`, `payment_intent.payment_failed`, `charge.refunded`).

## Estructura

```
hdbarber/
├── backend/        # API Laravel 11
│   ├── app/
│   │   ├── Http/Controllers/Api/   # Auth, Service, Appointment, Payment, Admin, StripeWebhook
│   │   ├── Http/Middleware/EnsureRole.php
│   │   ├── Models/                 # User, Service, Employee, Appointment, Payment, ActivityLog
│   │   └── Services/               # AppointmentService, StripeService
│   ├── bootstrap/app.php           # middleware api stateful + role alias + throttle
│   ├── config/                     # app, auth, sanctum, cors, services(stripe), session, ...
│   ├── database/migrations/        # users, services, employees, appointments, payments, ...
│   ├── database/seeders/           # admin + cliente demo, servicios y empleados
│   ├── routes/api.php              # rutas REST + webhook stripe
│   └── .env.example
└── frontend/       # SPA + PWA Vue 3
    ├── src/
    │   ├── api/client.ts           # axios + Sanctum cookie + CSRF
    │   ├── stores/                 # auth, services, appointments
    │   ├── router/                 # guards auth/admin
    │   ├── components/             # AppShell, ServiceCard, DatePicker
    │   └── views/                  # Home, Login, Register, Book, MyAppointments, Profile, admin/*
    ├── vite.config.ts              # PWA + proxy /api → :8000
    ├── tailwind.config.js
    └── .env.example
```

## Setup rápido

> **¿No tienes PHP/Composer/MySQL instalados en Windows?** Salta a la [opción Docker](#opción-docker-recomendado-en-windows). Es la forma más rápida.

### Opción Docker (recomendado en Windows)

Requiere **Docker Desktop** instalado y arrancado: https://www.docker.com/products/docker-desktop/

```powershell
# Desde la raíz del repo (c:\Users\diego\Downloads\hdbarber)
copy backend\.env.example backend\.env

# Construir y arrancar contenedores (db, app php-fpm, web nginx, mailhog, phpmyadmin)
docker compose up -d --build

# Instalar dependencias PHP dentro del contenedor app
docker compose exec app composer install

# Generar APP_KEY, ejecutar migraciones y seeders
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

Servicios disponibles tras `docker compose up`:

| Servicio    | URL                          | Descripción                    |
|-------------|------------------------------|--------------------------------|
| API Laravel | http://localhost:8000        | Backend (nginx + php-fpm)      |
| phpMyAdmin  | http://localhost:8081        | UI de MariaDB (root/root)      |
| MailHog     | http://localhost:8025        | Captura de emails salientes    |
| MariaDB     | `localhost:3308` (host)      | user `hdbarber` / pass `hdbarber` |

Comandos útiles:

```powershell
docker compose logs -f app          # logs Laravel
docker compose exec app sh          # shell dentro del contenedor
docker compose exec app php artisan tinker
docker compose down                 # parar
docker compose down -v              # parar + borrar BD
```

Para el **frontend** sigue necesitando Node.js en Windows (no está dockerizado para mejor DX con HMR):

```powershell
cd frontend
npm install
copy .env.example .env
npm run dev   # http://localhost:5173
```

Si tampoco tienes Node, instálalo desde https://nodejs.org/ (LTS) o con `winget install OpenJS.NodeJS.LTS`.

### Opción local (sin Docker)

Necesitas: **PHP 8.2+**, **Composer**, **MariaDB/MySQL**, **Node.js 20+**.

Recomendado en Windows: **Laragon Full** (https://laragon.org/download/) trae todo en uno.

```powershell
# 1. Backend
cd backend
composer install
copy .env.example .env
# Edita .env: DB_HOST=127.0.0.1, DB_USERNAME=root, DB_PASSWORD=...
# Crea la BD 'hdbarber' en tu MariaDB/MySQL
php artisan key:generate
php artisan migrate --seed
php artisan serve   # http://localhost:8000

# 2. Frontend (otra terminal)
cd frontend
npm install
copy .env.example .env
npm run dev   # http://localhost:5173
```

### Usuarios demo (seeder)

- **Admin** → `admin@hdbarber.test` / `Admin12345`
- **Cliente** → `cliente@hdbarber.test` / `Cliente12345`

### Iconos PWA

Necesitarás añadir 3 iconos PNG en `frontend/public/icons/` para que la PWA pase la auditoría:
- `icon-192.png` (192x192)
- `icon-512.png` (512x512)
- `icon-maskable-512.png` (512x512, purpose maskable)

### 3. Stripe (modo test)

1. Crea cuenta en https://stripe.com y obtén `pk_test_...` y `sk_test_...`.
2. Rellena en `backend/.env`:
   ```
   STRIPE_KEY=pk_test_...
   STRIPE_SECRET=sk_test_...
   STRIPE_WEBHOOK_SECRET=whsec_...
   ```
3. En desarrollo, redirige el webhook con Stripe CLI:
   ```powershell
   stripe listen --forward-to http://localhost:8000/api/webhooks/stripe
   ```
   Copia el `whsec_...` que muestra al `STRIPE_WEBHOOK_SECRET`.

Tarjeta de prueba: `4242 4242 4242 4242` · cualquier fecha futura · CVC cualquiera.

## Endpoints principales

### Públicos
- `GET  /api/services` — catálogo de servicios activos.
- `GET  /api/availability?service_id=&date=&employee_id=` — slots libres.
- `POST /api/auth/register` · `POST /api/auth/login` (rate limit 5/min).
- `POST /api/webhooks/stripe` — webhook (firma verificada, sin CSRF).

### Autenticados (Sanctum SPA cookie)
- `GET    /api/auth/me`, `PATCH /api/auth/profile`, `POST /api/auth/logout`
- `GET    /api/appointments` · `POST /api/appointments` · `GET /api/appointments/{id}` · `POST /api/appointments/{id}/cancel`
- `POST   /api/appointments/{id}/checkout` → URL de Stripe Checkout.
- `POST   /api/appointments/{id}/intent` → `client_secret` para Payment Element.
- `GET    /api/payments`

### Solo admin (`role:admin`)
- `GET    /api/admin/dashboard` · `GET /api/admin/clients`
- `PATCH  /api/admin/appointments/{id}`
- `POST/PATCH/DELETE /api/admin/services[/{id}]`
- `GET/POST/PATCH/DELETE /api/admin/employees[/{id}]`

## Seguridad implementada

- **Hash bcrypt** vía `'password' => 'hashed'` cast en `User`.
- **Sanctum SPA** con cookies **HTTP-only** + **CSRF** (`/sanctum/csrf-cookie`, header `X-XSRF-TOKEN` automático en axios).
- **Rate limiting** en `login`/`register` (5/min) y API global (60/min/usuario).
- **Validación estricta** en backend (`request->validate`) y password policy (min 8, mayús/min/núm).
- **Sanitización** con `strip_tags` para campos libres (`name`, `notes`).
- **CORS** restringido a `SPA_URL`, `supports_credentials=true`.
- **Webhooks Stripe** verificados con `Webhook::constructEvent` (firma + tolerancia 300s).
- **SQL Injection**: imposible vía Eloquent + binds. **XSS**: Vue escapa por defecto.
- **Cookies**: `HttpOnly=true`, `SameSite=Lax`, `Secure` en producción (`SESSION_SECURE_COOKIE=true`).
- **HTTPS obligatorio** en producción (configurar a nivel servidor / proxy).
- **Soft deletes** en `users` para cumplimiento.

## PWA

- Manifest auto-generado por `vite-plugin-pwa` con icons 192/512 + maskable, `display:standalone`, `theme_color` oscuro.
- Service Worker (Workbox) con estrategias:
  - `StaleWhileRevalidate` para `/api/services` (catálogo).
  - `CacheFirst` para imágenes.
  - Precaché de assets del build.
- `registerType: 'autoUpdate'` → actualiza en segundo plano.
- Instalable en móvil (Add to Home Screen). Funcionamiento offline básico para shell + servicios cacheados.

## Empaquetado nativo (opcional)

Para publicar como app nativa con Capacitor:

```powershell
cd frontend
npm i -D @capacitor/cli @capacitor/core @capacitor/android @capacitor/ios
npx cap init HDBarber com.hdbarber.app --web-dir=dist
npm run build
npx cap add android
npx cap copy
npx cap open android
```

## Sistema de reservas

- Slots de 15 min sobre `working_hours` (JSON por día) del empleado, con duración del servicio.
- `AppointmentService::ensureSlotFree` evita doble reserva (chequeo transaccional al crear).
- Cancelación permitida hasta **2h antes** (regla cliente). Admin puede cancelar siempre.
- Estados: `pending → confirmed → completed` (o `cancelled`/`no_show`).
- `payment_status`: `unpaid → paid` automático al recibir webhook.

## Sistema de pagos

1. Cliente reserva (`POST /api/appointments`) con `payment_status=unpaid`.
2. Cliente pulsa "Pagar" → backend crea **Stripe Checkout Session** y devuelve `url`.
3. Stripe redirige a `success_url` (incluye `session_id`).
4. **Webhook** recibe `checkout.session.completed` → `Payment.status=succeeded` y `Appointment.payment_status=paid`, `status=confirmed`.
5. Errores → `payment_failed` actualiza estado, cliente puede reintentar desde "Mis citas".
6. Refunds → `charge.refunded` marca `refunded` automáticamente.

## Producción (notas)

- Servir `frontend/dist` con Nginx/Caddy con HTTPS (Let's Encrypt).
- Backend detrás del mismo dominio o subdominio (`api.hdbarber.es`) con CORS y `SANCTUM_STATEFUL_DOMAINS` ajustados.
- `SESSION_SECURE_COOKIE=true`, `SESSION_SAME_SITE=lax` (o `none` si subdominios cruzados con HTTPS).
- Cola para emails: cambia `QUEUE_CONNECTION=redis` y lanza `php artisan queue:work`.
- Logs en `storage/logs/laravel.log` (rotación con `daily` channel disponible).

## Próximos pasos sugeridos

- [ ] Notificaciones push (Web Push API + VAPID).
- [ ] Emails transaccionales reales (Mailgun/Postmark) con plantillas Blade.
- [ ] Subida de imágenes (galería de trabajos) — disco S3.
- [ ] Tests Pest/PHPUnit para `AppointmentService` y webhooks.
- [ ] i18n (vue-i18n) ES/EN.
