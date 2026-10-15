# Checklist de Despliegue - FACTUS

## Pre-despliegue

### Variables de entorno

Copiar `.env.example` como `.env` y configurar:

```
APP_NAME=FACTUS
APP_ENV=production
APP_KEY=<generar con: php artisan key:generate>
APP_DEBUG=false
APP_URL=https://tu-dominio.com

DB_CONNECTION=pgsql
DB_HOST=<host-postgres-local>
DB_PORT=5432
DB_DATABASE=factus
DB_USERNAME=<usuario-db>
DB_PASSWORD=<password-db>

SESSION_DRIVER=database
SESSION_LIFETIME=60
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true

LOG_CHANNEL=stack
LOG_STACK=daily
LOG_LEVEL=info

# Facturación local (sin dependencia de API externa).
BILLING_PREFIX=FAC
BILLING_ISSUER_NAME=FACTUS
```

### Verificar antes de desplegar

- [ ] `APP_DEBUG=false` en producción
- [ ] `APP_KEY` generado y único
- [ ] `SESSION_ENCRYPT=true`
- [ ] `SESSION_SECURE_COOKIE=true` (requiere HTTPS)
- [ ] Credenciales de base de datos PostgreSQL local correctas
- [ ] `.env` NO está en el repositorio
- [ ] `.env.example` tiene valores seguros por defecto

### Base de datos (PostgreSQL local)

- [ ] Tablas creadas con las migraciones: `php artisan migrate --force`
- [ ] Seeder ejecutado: `php artisan db:seed`
- [ ] Primer usuario `super_admin` creado y verificado
- [ ] Tablas `sessions` y `cache` existen (para sesiones y colas)

### Storage

- [ ] `storage/app` con permisos de escritura
- [ ] `storage/logs` con permisos de escritura
- [ ] `storage/framework/sessions` con permisos de escritura
- [ ] `storage/framework/cache` con permisos de escritura

### Queue

- [ ] Cola configurada con `QUEUE_CONNECTION=database`
- [ ] Worker ejecutándose: `php artisan queue:work`
- [ ] Cron configurado para `php artisan schedule:run` (cada minuto)

## Despliegue

### Opción A: Traditional hosting

```bash
# 1. Clonar repositorio
git clone <repo-url> && cd factus-app

# 2. Instalar dependencias
composer install --optimize-autoloader --no-dev

# 3. Configurar entorno
cp .env.example .env
php artisan key:generate
# Editar .env con credenciales de producción

# 4. Base de datos
php artisan migrate --force
php artisan db:seed

# 5. Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Storage link
php artisan storage:link

# 7. Iniciar worker
nohup php artisan queue:work --sleep=3 --tries=3 &
```

### Opción B: Docker

```dockerfile
# Dockerfile incluido en el proyecto
docker build -t factus-app .
docker run -d --env-file .env -p 8000:8000 factus-app
```

### Opción C: Laravel Forge / Vapor

- Configurar variables de entorno en el panel
- Asegurar que las migraciones se ejecuten automáticamente
- Configurar el worker de colas

## Post-despliegue

### Verificación

- [ ] Aplicación accesible via HTTPS
- [ ] Login funcional con credenciales de prueba
- [ ] RBAC funciona (super_admin, admin, supervisor, cashier)
- [ ] Productos se listan correctamente
- [ ] Clientes se crean y editan
- [ ] POS funciona end-to-end
- [ ] Facturación emite localmente (CUFE y QR generados de forma local)
- [ ] Reportes generan datos correctos
- [ ] Logs se escriben en `storage/logs/`
- [ ] Cola procesa trabajos pendientes

### Monitoreo

- [ ] Verificar `storage/logs/laravel.log` periódicamente
- [ ] Monitorear tabla `billing_logs` para errores de facturación recurrentes
- [ ] Verificar que los workers de cola estén activos

### Seguridad post-despliegue

- [ ] Rotar `APP_KEY` si se comprometió
- [ ] Verificar que `APP_DEBUG=false`
- [ ] Revisar permisos de archivos en `storage/`
- [ ] Verificar que `.env` no es accesible públicamente
- [ ] Configurar backup automático de PostgreSQL (ej: `pg_dump` programado)
