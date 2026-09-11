# PDP — Plan Deploy Production

## Vibeer Backend (Laravel 8.83 + PostgreSQL 14)

> **Estrategia aprobada:** desplegar en un VPS usando el **stack Docker que ya existe** (`docker-compose.yml` + `Dockerfile` + `kool.yml`), con cambios mínimos de dev → prod. Sin scripts `.sh`, sin cron/scheduler/supervisord: todos los comandos artisan se ejecutan **manualmente (one-off)** cuando se requieren.

---

## Índice

1. [Pre-requisitos](#1-pre-requisitos)
2. [Certificados: staging (local) vs producción (real)](#2-certificados-staging-local-vs-producción-real)
3. [Variables de entorno: Dev → Prod](#3-variables-de-entorno-dev--prod)
4. [Seeders: solo AdminSeeder](#4-seeders-solo-adminseeder)
5. [Cambios mínimos ya aplicados (nginx 80/443 + compose)](#5-cambios-mínimos-ya-aplicados-nginx-80443--compose)
6. [Despliegue en el VPS — pasos one-off](#6-despliegue-en-el-vps--pasos-one-off)
7. [Comandos manuales (sin cron / scheduler / workers)](#7-comandos-manuales-sin-cron--scheduler--workers)
8. [Verificación post-deploy](#8-verificación-post-deploy)
9. [Seguridad — Checklist final](#9-seguridad--checklist-final)
10. [Resumen de archivos](#10-resumen-de-archivos)

---

## 1. Pre-requisitos

| Requisito | Especificación |
|-----------|---------------|
| VPS | Linux (Debian/Ubuntu), 1 vCPU, 512MB RAM (**swap 2GB obligatorio**), 30GB SSD, 1 IP |
| Docker | Docker Engine 20.10+ y Docker Compose v2 |
| Puertos | `80` y `443` abiertos en el firewall (HTTP/HTTPS) |
| Dominio | `api.vibeer.com` (API) y `vibeer.com` (SPA) con DNS A → IP del VPS |
| Certificado | Let's Encrypt (gratis, auto-renovable) o cert comprado — **un solo cert para los 2 dominios** |
| BD | PostgreSQL 14 en contenedor (el compose lo levanta) |
| Cola/cache | Drivers `file`/`sync` (decisión aprobada: cero cambios Docker). Redis queda opcional |
| Imágenes | **Construidas fuera del VPS** (`docker save`/`load`) — 512MB no dan para webpack/npm/composer en el host |
| MailHog | Solo dev (servicio con `profiles: ["dev"]`, activado con `COMPOSE_PROFILES=dev`) |

> **Nota de la simulación:** ya se validó que si se usara **Redis** para cache/sesión, el `Dockerfile` original **no incluye la extensión PHP `redis`** (`Class "Redis" not found`). Por eso se aprobó usar drivers `file`/`sync` en el VPS. Si en el futuro se quiere Redis, agregar al Dockerfile:
> `RUN pecl install redis && docker-php-ext-enable redis`

---

## 2. Certificados: staging (local) vs producción (real)

| | Staging (local, `vibeer.test`) | Producción (`vibeer.com` / `api.vibeer.com`) |
|---|---|---|
| Certificado | Self-signed (openssl, CN `*.vibeer.test`) — **solo para simular HTTPS en local** | Let's Encrypt o comprado por la empresa |
| DNS | Entry en `C:\Windows\System32\drivers\etc\hosts` → `127.0.0.1 cosmos.vibeer.test api.vibeer.test` | DNS real en el panel del dominio |
| Ruta en nginx | `/certs/...` (eliminados del repo tras la simulación) | `/certs/real/fullchain.pem` + `/certs/real/privkey.pem` — **un solo cert para ambos dominios** |

**No hay conflicto entre ambos**: son dominios y escenarios distintos. En producción el nginx apunta a los certs reales (montados en `/certs/real`) y cambia el `server_name`. El flujo exacto (comandos completos en `DEPLOY.md` §4):

- **Primera emisión (antes del primer `up`)** — el block 443 exige los PEM para arrancar, así que se usa `--standalone` con el `:80` libre:
  ```bash
  cd /var/www/backendMusic
  certbot certonly --standalone -d api.vibeer.com -d vibeer.com \
    --register-unsafely-without-email --agree-tos
  cp -L /etc/letsencrypt/live/api.vibeer.com/{fullchain.pem,privkey.pem} Docker/certs/real/
  ```
- **Renovaciones (sin detener nada)** — el block `:80` sirve `/.well-known/acme-challenge/` desde `Docker/certs/real`, y un `--deploy-hook` copia los PEM nuevos y recarga nginx:
  ```bash
  certbot certonly --webroot -w /var/www/backendMusic/Docker/certs/real \
    -d api.vibeer.com -d vibeer.com \
    --deploy-hook 'cp -L /etc/letsencrypt/live/api.vibeer.com/{fullchain.pem,privkey.pem} /var/www/backendMusic/Docker/certs/real/ && docker compose -f /var/www/backendMusic/docker-compose.yml restart nginx'
  certbot renew   # reusa método+opciones registradas; auto vía certbot.timer
  ```
  > (Alternativa: cert comprado → colocar `fullchain.pem` + `privkey.pem` en `Docker/certs/real/` manualmente)

- El folder `Docker/certs/real/` está montado como `/certs/real` dentro del nginx (ver 5). Certbot solo escribe en `/etc/letsencrypt`; el `--deploy-hook` copia los `.pem` nuevos al folder montado y recarga nginx (ver `DEPLOY.md` §4).

---

## 3. Variables de entorno: Dev → Prod

### CAMBIOS CRÍTICOS (SEGURIDAD — DEBEN CAMBIARSE)

| Variable | Dev | Prod | Impacto |
|----------|-----|------|---------|
| `APP_ENV` | `local` | `production` | Optimizaciones, oculta errores |
| `APP_DEBUG` | `true` | `false` | **No exponer stack traces** |
| `APP_KEY` | *(local)* | **Generar nueva** (`key:generate --force`) | No compartir ambientes |
| `APP_URL` | `http://localhost:8000` | `https://api.vibeer.com` | URLs generadas por Laravel |
| `DB_HOST` | `postgres` | `postgres` (servicio del compose) | Conexión |
| `DB_DATABASE` | `estadiauttec` | `vibeer_production` | Nombre de BD |
| `DB_USERNAME` | `postgres` | `vibeer` | Usuario |
| `DB_PASSWORD` | `password` | **Fuerte (20+ chars)** | **SEGURIDAD** |
| `JWT_SECRET` | *(local)* | **Generar nueva** (`jwt:secret --force`) | Tokens JWT |

### URLs del frontend

| Variable | Dev | Prod |
|----------|-----|------|
| `FRONTEND_APP` | `http://localhost:8080` | `https://vibeer.com` ← **usada por CORS y por la app** |
| `FRONTEND_URL` | *(no existe)* | `https://vibeer.com` (redirects en `routes/web.php`) |

### EMAIL (OBLIGATORIO — no dejar MailHog)

| Variable | Dev | Prod |
|----------|-----|------|
| `MAIL_MAILER` | `smtp` | `smtp` |
| `MAIL_HOST` | `mailhog` | `smtp.gmail.com` o proveedor real |
| `MAIL_PORT` | `1025` | `587` |
| `MAIL_USERNAME` | `null` | Cuenta real |
| `MAIL_PASSWORD` | `null` | App password |
| `MAIL_ENCRYPTION` | `null` | `tls` |
| `MAIL_FROM_ADDRESS` | `vibeertesting@gmail.com` | `noreply@vibeer.com` |

### OAUTH (OBLIGATORIO)

| Variable | Dev | Prod |
|----------|-----|------|
| `GOOGLE_REDIRECT_URL` | `http://localhost:8080/authorize/google/callback` | `https://vibeer.com/authorize/google/callback` |
| `FACEBOOK_REDIRECT_URL` | `http://localhost:8080/authorize/facebook/callback` | `https://vibeer.com/authorize/facebook/callback` |
| `GOOGLE_OAUTH_ID/KEY`, `FACEBOOK_CLIENT_ID/SECRET` | dev | credenciales de producción (registrar la redirect URI en cada consola) |

### CACHE / COLA (decisión aprobada — sin cambios Docker)

| Variable | Dev | Prod | Razón |
|----------|-----|------|-------|
| `CACHE_DRIVER` | `file` | `file` | Simple, sin ext-redis requerida |
| `SESSION_DRIVER` | `file` | `file` | Ídem |
| `SESSION_SECURE_COOKIE` | *(unset)* | `true` | Forzar cookies solo por HTTPS |
| `QUEUE_CONNECTION` | `sync` | `sync` | Jobs síncronos; puntuales → `queue:work --once` manual |
| `LOG_LEVEL` | `debug` | `warning` | No loguear debug en prod |
| `BROADCAST_DRIVER` | `log` | `log` | Sin real-time por ahora |

### SIN CAMBIO NECESARIO

| Variable | Valor | Notas |
|----------|-------|-------|
| `GOOGLE_MAPS_API_KEY` | `AIzaSyBBqTT...` | Restringir por dominio en Google Cloud Console |
| `OPENPAY_ID/SECRET/PRODUCTION_MODE` | `TU_OPENPAY_ID` | **Variables muertas** — OpenPay se configura vía tabla `openpay_keys` desde el admin |

---

## 4. Seeders: solo AdminSeeder

**Decisión aprobada:** en producción se siembra **solo un admin**. Clientes y artistas se crean con normalidad dentro de la app (registro/alta de perfil), no se siembran datos demo.

Archivo creado: `database/seeders/AdminSeeder.php`

```php
class AdminSeeder extends Seeder
{
    public function run()
    {
        $this->call(RoleSeeder::class); // Roles(3) + permisos(~25) — base del RBAC

        DB::statement('TRUNCATE TABLE users RESTART IDENTITY CASCADE;');

        $admin = User::create([
            'name'     => 'Administrador Vibeer',
            'email'    => 'admin@vibeer.com',
            'password' => bcrypt('password'),
        ]);
        $admin->roles()->sync(1); // Administrador
    }
}
```

Ejecución (1ª vez):

```bash
docker compose exec backend php artisan db:seed --class=AdminSeeder --force
```

| Email | Rol | Password inicial |
|-------|-----|------------------|
| `admin@vibeer.com` | Administrador | `password` → **cambiar en el 1er login** |

> **NO ejecutar** `UserSeeder`, `ArtistSeeder` ni el resto (datos demo). `DatabaseSeederProd`/`UserSeederProd`/`ArtistSeederProd` (creados durante la simulación local) ya se eliminaron del repo.

---

## 5. Cambios mínimos ya aplicados (nginx único 80/443 + compose)

Son los únicos archivos originales modificados para dejar el stack listo para el VPS:

### `Docker/nginx/default.conf`

Ahora tiene **4 server blocks**:
1. `listen 8000` — **desarrollo local** intacto (servicio original, subida `51g`).
2. `listen 80` — redirige **ambos dominios** (`api.vibeer.com vibeer.com`) → HTTPS.
3. `listen 443 ssl http2` — **API** (`server_name api.vibeer.com`): fastcgi a `backend:9000`, `client_max_body_size 100M`, headers, gzip, bloqueo de archivos ocultos.
4. `listen 443 ssl http2` — **SPA** (`server_name vibeer.com`): sirve `root /var/www/spa` (el `dist` del frontend montado), SPA fallback (`try_files ... /index.html`), cache agresiva en `/assets/` e `index.html` no-cache.

Ambos blocks 443 usan el **mismo cert** (`/certs/real/fullchain.pem` + `privkey.pem`).

### `docker-compose.yml`

```yaml
nginx:
    ports:
        - "8000:8000"   # dev local
        - "80:80"       # prod http -> https
        - "443:443"     # prod https
    volumes:
        - ./:/var/www
        - ./Docker/nginx/:/etc/nginx/conf.d/
        - ./Docker/certs:/certs:ro        # certs reales (Let's Encrypt)
        - ./spa:/var/www/spa:ro           # dist del frontend (extraido de la imagen)

mailhog:
    profiles: ["dev"]                     # solo desarollo local
```

> **Sin otros cambios:** el `Dockerfile` de dev se mantiene (drivers `file`/`sync` no requieren ext-redis). MailHog arranca solo si `COMPOSE_PROFILES=dev` (o `--profile dev`) — ver `.env.example` vs `.env.production.example`. El postgres del compose expone `5432` (útil para dev); opcional cerrarlo en el VPS.

---

## 6. Despliegue en el VPS — pasos one-off

> Todos los comandos son **manuales y en orden** (sin scripts `.sh`). `kool start` equivale a `docker compose up -d` — usaremos `docker compose` directo por claridad.

```bash
# 0. Pre-requisitos del VPS (una sola vez)
ssh root@vps
sudo apt update && sudo apt install -y docker.io docker-compose-plugin certbot
sudo fallocate -l 2G /swapfile && sudo chmod 600 /swapfile   # 512MB RAM -> swap obligatorio
sudo mkswap /swapfile && sudo swapon /swapfile
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab

# 0b. Llevar imagenes PRECONSTRUIDAS (build fuera del VPS):
#   docker save backendmusic-backend | gzip > vibeer-backend.tar.gz
#   docker save vibeer-frontend:prod  | gzip > vibeer-frontend.tar.gz
#   scp ambos a /var/www/ y luego: gzip -dc ... | docker load

# 1. Clone del repo
git clone <repo> /var/www/backendMusic && cd /var/www/backendMusic

# 2. Usuario del contenedor corre como uid 1000 (Dockerfile) — asegurar permisos
sudo chown -R 1000:1000 /var/www/backendMusic

# 3. Variables de producción (plantilla lista para llenar, NUNCA con COMPOSE_PROFILES):
cp .env.production.example .env
nano .env   # APP_ENV=production, APP_DEBUG=false, APP_URL=https://api.vibeer.com,
            # FRONTEND_APP=https://vibeer.com, DB_*, MAIL_*, OAuth redirects, SESSION_SECURE_COOKIE=true

# 4. Levantar el stack SIN construir (imágenes cargadas; MailHog no corre sin COMPOSE_PROFILES)
docker compose up -d

# 5. Dependencias de producción (dentro del contenedor)
docker compose exec backend composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# 6. BORRAR caches de desarrollo commiteadas (referencian facade/ignition y rompen en prod)
docker compose exec backend sh -c "rm -f bootstrap/cache/packages.php bootstrap/cache/services.php"

# 7. Claves
docker compose exec backend php artisan key:generate --force
docker compose exec backend php artisan jwt:secret --force

# 8. Migraciones y seed de admin (solo 1ª vez)
docker compose exec backend php artisan migrate --force
docker compose exec backend php artisan db:seed --class=AdminSeeder --force

# 9. Storage symlink + caches
docker compose exec backend php artisan storage:link
docker compose exec backend php artisan config:cache
docker compose exec backend php artisan route:cache
docker compose exec backend php artisan view:cache
docker compose exec backend php artisan cache:clear

# 10. Frontend: extraer el dist de la imagen al volumen del nginx (puede hacerse antes o después)
mkdir -p spa
docker run --rm -v /var/www/backendMusic/spa:/out vibeer-frontend:prod \
  sh -c "cp -r /usr/share/nginx/html/. /out/"

# 11. Certificado real (ver sección 2 / DEPLOY.md §4) — un solo cert para los 2 dominios.
#     PRIMERA EMISIÓN con --standalone ANTES del primer up (el 443 exige los PEM);
#     renovaciones con --webroot + --deploy-hook.
sudo certbot certonly --standalone -d api.vibeer.com -d vibeer.com --register-unsafely-without-email --agree-tos
sudo cp -L /etc/letsencrypt/live/api.vibeer.com/fullchain.pem \
          /etc/letsencrypt/live/api.vibeer.com/privkey.pem Docker/certs/real/
docker compose restart nginx

# 12. Verificar (ver sección 8)
curl -s https://api.vibeer.com/api/me -H "Accept: application/json" -w "\nHTTP %{http_code}\n"   # 401 = OK
curl -I https://vibeer.com
```

### Actualizaciones futuras (one-off)

```bash
cd /var/www/backendMusic
git pull origin main
# Recargar imágenes nuevas en el VPS (tarballs) y re-extraer el dist:
docker load < /var/www/vibeer-backend.tar.gz
docker load < /var/www/vibeer-frontend.tar.gz
docker run --rm -v /var/www/backendMusic/spa:/out vibeer-frontend:prod \
  sh -c "cp -r /usr/share/nginx/html/. /out/"
docker compose up -d --no-build
docker compose exec backend composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
docker compose exec backend php artisan migrate --force
docker compose exec backend php artisan config:cache
```

---

## 7. Comandos manuales (sin cron / scheduler / workers)

`Console\Kernel::schedule()` se deja **vacío**. No hay cron, ni contenedores `scheduler`/`worker`, ni supervisord. Se ejecutan **con la mano** cuando corresponda:

| Comando | Función | Cuándo |
|---------|---------|--------|
| `php artisan events:send-reminders` | Email 24h antes del evento | A demanda / despliegue |
| `php artisan events:send-completed-reminders` | Recordatorio de completar evento 24h después | A demanda |
| `php artisan events:send-artist-hour-reminders` | Recordatorio al artista 30 min antes | A demanda |
| `php artisan sanctions:lift-expired` | Reactivar cuentas con sanción expirada | A demanda |
| `php artisan approvals:expire` | Expirar aprobaciones >24h + reembolso OpenPay | A demanda |
| `php artisan queue:work --once` | Procesar **un** job de cola y salir | Puntual (cola en `sync`) |

> Si en el futuro la empresa quiere automatizar: agregar bloque `schedule()` en `Kernel.php` + un cron `* * * * * php artisan schedule:run`. No es requisito.

---

## 8. Verificación post-deploy

```bash
# 1. La app responde (debe dar 200/301)
curl -I https://api.vibeer.com/api/health

# 1b. La SPA responde en el mismo nginx (server block vibeer.com)
curl -sI https://vibeer.com | head -1
curl -sI https://vibeer.com/login | head -1   # 200 (SPA fallback, no 404)
curl -I http://vibeer.com | grep -iE "^HTTP|location"   # 301 -> https

# 2. BD conectada
docker compose exec backend php artisan tinker --execute="DB::connection()->getPdo(); echo 'OK';"

# 3. Roles y permisos
docker compose exec backend php artisan tinker --execute="echo \App\Models\Role::count();"

# 4. Admin existe
docker compose exec backend php artisan tinker --execute="echo \App\Models\User::count();"

# 5. Cache funciona (driver file)
docker compose exec backend php artisan tinker --execute="cache()->put('test','ok',60); echo cache()->get('test');"

# 6. Login real contra el API
curl -sk -X POST https://api.vibeer.com/api/login \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -H "Origin: https://vibeer.com" \
  -d '{"email":"admin@vibeer.com","password":"password"}'
# esperado: {"success":true,"access_token":"..."}

# 7. Comandos manuales responden
docker compose exec backend php artisan events:send-reminders --help

# 8. Logs
tail -f storage/logs/laravel.log
```

### Errores comunes

| Error | Causa | Solución |
|-------|-------|----------|
| `HTTP 500` | `APP_DEBUG=false` | Revisar `storage/logs/laravel.log` |
| `Class "facade/ignition" not found` | `bootstrap/cache/services.php` commiteado (dev) | `rm -f bootstrap/cache/packages.php bootstrap/cache/services.php` y `config:cache` |
| `Class "Redis" not found` | Se activó redis sin ext-redis en `Dockerfile` | Usar drivers `file` (aprobado) o agregar `pecl install redis` |
| `CSRF/CORS` | `FRONTEND_APP` mal | Verificar `FRONTEND_APP` en `.env` y `config/cors.php` |
| `JWT invalid` | `JWT_SECRET` no coincide | `php artisan jwt:secret --force` |
| `OpenPay charges fail` | Sandbox activo | Admin panel → OpenPay Keys → desactivar sandbox |
| `Emails no llegan` | Sigue MailHog / SMTP mal | Cambiar `MAIL_HOST/PORT/USERNAME/PASSWORD` reales |
| `OAuth redirect mismatch` | Redirect URI no registrada | Actualizar `GOOGLE_REDIRECT_URL`/`FACEBOOK_REDIRECT_URL` en `.env` y en las consolas |
| `Storage 404` | Falta symlink | `php artisan storage:link` |
| `vibeer.com 404/502` | `spa/` vacío o sin `dist` | Re-extraer el dist: `docker run --rm -v /var/www/backendMusic/spa:/out vibeer-frontend:prod sh -c "cp -r /usr/share/nginx/html/. /out/"`
| `nginx no arranca` | Falta cert en `/certs/real` | Correr certbot (sección 2) y `docker compose restart nginx` |

---

## 9. Seguridad — Checklist final

- [ ] `APP_DEBUG=false`
- [ ] `APP_KEY` y `JWT_SECRET` nuevos (`--force`)
- [ ] `DB_PASSWORD` fuerte (20+ caracteres)
- [ ] Password de `admin@vibeer.com` cambiado tras el 1er login
- [ ] Redirect URIs de OAuth actualizadas en Google/Facebook
- [ ] SMTP real (no MailHog)
- [ ] **MailHog apagado en prod** (sin `COMPOSE_PROFILES` en el `.env`)
- [ ] Google Maps API key restringida por dominio
- [ ] HTTPS activo con cert **real** para los 2 dominios en `/certs/real` (no self-signed)
- [ ] nginx único prod: block API (`api.vibeer.com`) y block SPA (`vibeer.com`), `client_max_body_size=100M`, headers, bloqueo de archivos ocultos
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] `.env` nunca commiteado
- [ ] Imágenes precargadas con `docker load` (no build en el VPS de 512MB)
- [ ] Swap activo (`swapon --show`)
- [ ] Scheduler/queue **manuales** (sin cron/supervisord/worker)
- [ ] (Opcional VPS) postgres del compose sin exponer `5432` públicamente

---

## 10. Resumen de archivos

| Archivo | Acción | Estado | Descripción |
|---------|--------|--------|-------------|
| `database/seeders/AdminSeeder.php` | CREAR | ✅ hecho | Roles + 1 admin (sección 4) |
| `Docker/nginx/default.conf` | MODIFICAR | ✅ hecho | 4 blocks: dev(8000) + 80 (2 dominios→https) + 443 API + 443 SPA `/var/www/spa` |
| `docker-compose.yml` | MODIFICAR | ✅ hecho | Puertos 80/443, volúmenes certs + `./spa`; MailHog `profiles: ["dev"]` |
| `Docker/certs/real/.gitkeep` | CREAR | ✅ hecho | Punto de montaje para certs reales (renovación certbot) |
| `.env.production.example` | CREAR | ✅ hecho | Plantilla de variables para llenar en el VPS (sin `COMPOSE_PROFILES`) |
| `.env.example` | MODIFICAR | ✅ hecho | Agrega `COMPOSE_PROFILES=dev` (MailHog en dev) |
| `.gitignore` | MODIFICAR | ✅ hecho | Excluye `/spa` |
| `.env` (VPS) | CREAR | en deploy | Copiar de `.env.production.example` (nunca commiteado) |
| `Console/Kernel.php` | SIN CAMBIO | — | `schedule()` vacío, comandos manuales |
| `Dockerfile` | SIN CAMBIO | — | Drivers `file`/`sync` no requieren ext-redis |

**Eliminados durante la simulación (ya no existen):** `Dockerfile.prod`, `docker-compose.staging.yml`, `.env.staging`, `Docker/nginx/staging.conf`, `Docker/certs/*` (self-signed), `DatabaseSeederProd/UserSeederProd/ArtistSeederProd`.