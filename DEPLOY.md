# Deploy Vibeer en el VPS único — Cheat-sheet

VPS de referencia: 1 vCPU, 512MB RAM, 30GB SSD, 1 IP, Ubuntu/Debian.
Backend (`api.vibeer.com`) y Frontend (`vibeer.com`) viven en el MISMO host con **un solo nginx público** (SNI por dominio). Detalle: `PDP_Vibeer.md` y `PDP_Frontend.md`.

> Los comandos del VPS se ejecutan **como root** (típico droplet). Si tu usuario no es root, antepón `sudo`.

## 0. Manifest de archivos — guía definitiva (qué va dónde y de dónde sale)

> **Regla de oro**: al VPS **solo** viajan el repo backend (`backendMusic/`) y **dos imágenes**: `backendmusic-backend` y `vibeer-frontend:prod`. El repo frontend **no se copia jamás al VPS**: de él solo se transfiere su imagen (tar.gz). En el VPS el contenedor del frontend **nunca corre**; quien sirve la SPA es el nginx del backend, leyendo `backendMusic/spa/`.

### Tabla A — Archivos que deben existir en el VPS (`/var/www/backendMusic/`)

| Archivo | Origen | Qué es / rol en el deploy |
|---|---|---|
| `docker-compose.yml` | git | Pila completa: `postgres`, `backend` (php-fpm), `nginx` (público: 8000/80/443), `mailhog` (solo perfil `dev`). Monta `./` en `/var/www`, `./Docker/nginx/` en `/etc/nginx/conf.d/`, `./Docker/certs` en `/certs:ro` y `./spa` en `/var/www/spa:ro`. |
| `Dockerfile` | git | Imagen php-fpm (uid 1000, `pdo_pgsql`, composer, `Docker/php/custom.ini` embebido). Se construye en tu máquina, NO en el VPS; llega como imagen `backendmusic-backend`. |
| `Docker/nginx/default.conf` | git | Los 4 server blocks: dev `:8000` · `:80` con redirect 301 + `/.well-known/acme-challenge/` · API 443 · SPA 443 (root `/var/www/spa`, fallback SPA). Montado por compose en `/etc/nginx/conf.d/`. |
| `Docker/php/custom.ini` | git | Ajustes PHP — van dentro de la imagen (build), sin acción en el VPS. |
| `.env.production.example` | git | Plantilla de variables para producción. |
| `.env` | **CREAR** | `cp .env.production.example .env` + rellenar: DB fuertes (`postgres` / `vibeer_production`), `FRONTEND_APP=https://vibeer.com`, SMTP real, OAuth/Google/FB, Maps. `APP_KEY` y `JWT_SECRET` se generan abajo con `--force`. |
| `Docker/certs/real/fullchain.pem` + `privkey.pem` | **MOV ER/CREAR** | PEM de certbot `--standalone` (o cert comprado) copiados con `cp -L` desde letsencrypt. Gitignored. **El block 443 no arranca sin ellos**; nginx los ve en `/certs/real/`. |
| `spa/` (contenido completo) | **EXTRAER** | Desde la imagen `vibeer-frontend:prod` (`docker run ... cp -r /usr/share/nginx/html/. → /out`) + `chown -R 1000:1000`. Gitignored. |
| `bootstrap/cache/*`, claves de `.env` | **GENERAR** | Comandos artisan del primer boot: `composer install`, `key:generate --force`, `jwt:secret --force`, `migrate --force`, seed, `storage:link`, `config/route/view:cache` (§3). |

### Tabla B — Lo que viaja como imagen (el repo frontend jamás se copia al VPS)

| Imagen | Se produce (en tu máquina) | Se transfiere como | En el VPS |
|---|---|---|---|
| `backendmusic-backend` | `docker compose build` (en `backendMusic/`) | `vibeer-backend.tar.gz` (`docker save` + gzip + scp) | `docker load` → php-fpm que atiende los blocks API y SPA vía el nginx público |
| `vibeer-frontend:prod` | `docker compose -f docker-compose.prod.yml build` (en `frontendMusic/`) | `vibeer-frontend.tar.gz` | `docker load` → **solo** se extrae `dist/spa` a `backendMusic/spa/`; el contenedor no corre |

Archivos del repo **frontend** que intervienen en el build (**no van al VPS**):
- `Dockerfile.prod` — multi-stage: `node:18-alpine` hace `npm ci` + `npx quasar build`; la etapa final `nginx:1.25-alpine` copia `/app/dist/spa` a `/usr/share/nginx/html` y su `Docker/nginx/default.conf` (SPA TLS) **dentro** de la imagen. Ese nginx interno solo existe como fuente del dist — el que responde en el VPS es el del backend.
- `docker-compose.prod.yml` — build: `ARG VUE_APP_API_URL=https://api.vibeer.com`, `image: vibeer-frontend:prod`.
- `Docker/nginx/dev.conf` — solo dev local (SPA HTTP, API `http://localhost:8000`); nunca llega al VPS.

### Tabla C — Movimientos (transferencias) en orden

1. Tu máquina → VPS: `scp vibeer-backend.tar.gz vibeer-frontend.tar.gz root@IP:/var/www/`.
2. VPS: `docker load -i` ×2 (§2).
3. Cert primero: `certbot certonly --standalone -d api.vibeer.com -d vibeer.com` → `cp -L` PEM → `Docker/certs/real/` (§5a).
4. `docker compose up -d` → comandos artisan (§3).
5. Extraer SPA: `docker run --rm -v /var/www/backendMusic/spa:/out vibeer-frontend:prod sh -c "cp -r /usr/share/nginx/html/. /out/"` + `chown -R 1000:1000` (§4).
6. Verificar (§6) + checklist de "firma" (abajo).

### Checklist de "firma" (todo debe existir)

```bash
cd /var/www/backendMusic
test -f docker-compose.yml && echo OK docker-compose.yml
test -f Docker/nginx/default.conf && echo OK default.conf
test -f Docker/certs/real/fullchain.pem && test -f Docker/certs/real/privkey.pem && echo OK certs
test -f .env && echo OK .env
test -n "$(ls -A spa/)" && echo "OK spa/ (dist extraido)"
docker image ls backendmusic-backend:latest vibeer-frontend:prod
```

**Persistencia (importante)**: `docker-compose.yml` **no declara** volumen para postgres → usa un volumen **anónimo** que puede perderse en `up --force-recreate` o restart de WSL (ver §8). Recomendación: declarar un volumen con nombre (p. ej. `pgdata`) **antes** del deploy real.

## 1. Pre-requisitos del VPS (una sola vez)

```bash
sudo apt update && sudo apt install -y docker.io docker-compose-plugin certbot

# Swap OBLIGATORIO (512MB de RAM son críticos):
sudo fallocate -l 2G /swapfile && sudo chmod 600 /swapfile
sudo mkswap /swapfile && sudo swapon /swapfile
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab

# Firewall: abrir 80 y 443.
# DNS: api.vibeer.com y vibeer.com -> A -> IP del VPS.
```

## 2. Construir y llevar las imágenes (desde tu máquina, NO en el VPS)

512MB NO dan para compilar webpack/composer en el VPS. Se construyen en tu máquina y se transfieren:

```bash
# Backend (php-fpm + composer)
cd /mnt/e/RootHeim/backendMusic && docker compose build
docker save backendmusic-backend | gzip > vibeer-backend.tar.gz

# Frontend (node build -> imagen con dist/spa)
cd /mnt/e/RootHeim/frontendMusic && docker compose -f docker-compose.prod.yml build
docker save vibeer-frontend:prod | gzip > vibeer-frontend.tar.gz

# Enviar al VPS
scp vibeer-backend.tar.gz vibeer-frontend.tar.gz root@IP:/var/www/
```

```bash
# En el VPS (cargar sin compilar):
cd /var/www
sudo docker load -i vibeer-backend.tar.gz
sudo docker load -i vibeer-frontend.tar.gz
```

## 3. Backend — primer despliegue

**El certificado hay que emitirlo ANTES de `docker compose up -d`** (el block 443 de nginx exige los PEM para arrancar). El orden importa: primero el cert (paso §5a), luego el stack.

```bash
cd /var/www
sudo git clone <repo-backend> backendMusic && cd backendMusic
sudo chown -R 1000:1000 .          # uid del Dockerfile

cp .env.production.example .env
nano .env   # DB_DATABASE/DB_USERNAME/DB_PASSWORD fuertes, MAIL real,
            # GOOGLE_OAUTH_ID/KEY, FACEBOOK_CLIENT_ID/SECRET, GOOGLE_MAPS_API_KEY.
            # APP_KEY y JWT_SECRET se generan abajo con --force.

# ---- CERTIFICADO PRIMERO (ver §5a): ---- (puerto 80 libre, stack aún no corre)
sudo certbot certonly --standalone -d api.vibeer.com -d vibeer.com \
  --register-unsafely-without-email --agree-tos
sudo cp -L /etc/letsencrypt/live/api.vibeer.com/fullchain.pem \
          /etc/letsencrypt/live/api.vibeer.com/privkey.pem \
          Docker/certs/real/
# ls Docker/certs/real/fullchain.pem privkey.pem   # deben existir

# ---- Ya con certs, levanta el stack ----
docker compose up -d                # MailHog NO corre (sin COMPOSE_PROFILES)

docker compose exec backend composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
docker compose exec backend sh -c "rm -f bootstrap/cache/packages.php bootstrap/cache/services.php"
docker compose exec backend php artisan key:generate --force
docker compose exec backend php artisan jwt:secret --force
docker compose exec backend php artisan migrate --force
docker compose exec backend php artisan db:seed --class=AdminSeeder --force
docker compose exec backend php artisan storage:link
docker compose exec backend php artisan config:cache
docker compose exec backend php artisan route:cache
docker compose exec backend php artisan view:cache
docker compose exec backend php artisan cache:clear
```

> Admin: `admin@vibeer.com` / `password` -> **cambiar en el primer login**.

## 4. Frontend — extraer el dist (sin dejar contenedor corriendo)

```bash
cd /var/www && mkdir -p backendMusic/spa
# Copia el dist/spa de la imagen ya cargada al volumen del nginx del backend:
docker run --rm -v /var/www/backendMusic/spa:/out vibeer-frontend:prod \
  sh -c "cp -r /usr/share/nginx/html/. /out/"
sudo chown -R 1000:1000 backendMusic/spa
```

## 5. Certificado real — 2 fases

Un SOLO cert para los dos dominios. nginx monta `./Docker/certs:/certs:ro`; el nginx espera los PEM en `/certs/real/{fullchain,privkey}.pem`.

### 5a. Primera emisión — `--standalone` (una vez, ANTES del primer `up`)

El block 443 no arranca sin los PEM, así que esto se hace con el stack detenido (el `:80` del host libre):

```bash
cd /var/www/backendMusic
sudo certbot certonly --standalone -d api.vibeer.com -d vibeer.com \
  --register-unsafely-without-email --agree-tos

# Copiar los PEM al sitio que nginx espera (y está gitignored):
sudo cp -L /etc/letsencrypt/live/api.vibeer.com/fullchain.pem \
          /etc/letsencrypt/live/api.vibeer.com/privkey.pem \
          Docker/certs/real/
docker compose up -d   # recién aquí nginx arranca con el block 443 ya válido
```

Alternativa (cert comprado): colocar el `fullchain.pem` + `privkey.pem` en `Docker/certs/real/` y saltarte certbot.

### 5b. Renovaciones — `--webroot` (sin detener nada)

El block `:80` ya sirve `/.well-known/acme-challenge/` desde `Docker/certs/real` (configurado en `Docker/nginx/default.conf`). Certbot escribe los retos ahí, y con un `--deploy-hook` copia los PEM nuevos y recarga nginx automáticamente.

```bash
# Primera renovación (deja registradas las opciones + el deploy-hook en
# /etc/letsencrypt/renewal/api.vibeer.com.conf; luego `certbot renew` las reusa):
cd /var/www/backendMusic
sudo certbot certonly --webroot -w /var/www/backendMusic/Docker/certs/real \
    -d api.vibeer.com -d vibeer.com \
    --deploy-hook 'cp -L /etc/letsencrypt/live/api.vibeer.com/fullchain.pem \
    /etc/letsencrypt/live/api.vibeer.com/privkey.pem \
    /var/www/backendMusic/Docker/certs/real/ && \
    docker compose -f /var/www/backendMusic/docker-compose.yml restart nginx'

# Próximas renovaciones (método + webroot + hook ya quedan guardados):
sudo certbot renew                     # solo renueva si falta <30 días

# O manual y explícito (idempotente, seguro de correr cuando quieras):
sudo certbot renew --webroot -w /var/www/backendMusic/Docker/certs/real
sudo cp -L /etc/letsencrypt/live/api.vibeer.com/fullchain.pem \
          /etc/letsencrypt/live/api.vibeer.com/privkey.pem \
          /var/www/backendMusic/Docker/certs/real/
docker compose -f /var/www/backendMusic/docker-compose.yml restart nginx
```

**Auto-renovación**: el paquete `certbot` instala el timer de sistema `certbot.timer` (no es cron de la app) que corre `certbot renew` a diario — usará el método/`--deploy-hook` de la última emisión, copiará los PEM y recargará nginx.
- Estado: `systemctl status certbot.timer` · proxima corrida: `systemctl list-timers certbot.timer`
- Si NO quieres auto-renovación: `sudo systemctl disable --now certbot.timer` y renueva a mano con el comando "manual y explícito".

## 6. Verificación

```bash
curl -s https://api.vibeer.com/api/me -H "Accept: application/json" -w "\nHTTP %{http_code}\n"   # 401 = OK (pide token)
curl -sI https://vibeer.com | head -1                  # 200 (SPA)
curl -sI https://vibeer.com/login | head -1            # 200 (SPA fallback)
curl -I http://api.vibeer.com | grep -iE "^HTTP|location"   # 301 -> https
curl -s https://api.vibeer.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@vibeer.com","password":"password"}' | head -c 200   # token JWT
curl -sI https://vibeer.com/.well-known/acme-challenge/ | head -1          # reto acme servido (previo al 301)
```

## 7. Actualizaciones futuras (one-off)

```bash
# 1) En tu máquina: rebuild + save + scp (repite el paso 1).
# 2) En el VPS:
cd /var/www/backendMusic && git pull origin main
sudo docker load -i /var/www/vibeer-backend.tar.gz    # nueva imagen backend
sudo docker load -i /var/www/vibeer-frontend.tar.gz   # nuevo dist
docker run --rm -v /var/www/backendMusic/spa:/out vibeer-frontend:prod \
  sh -c "cp -r /usr/share/nginx/html/. /out/"
sudo chown -R 1000:1000 spa
docker compose up -d --no-build
docker compose exec backend composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
docker compose exec backend php artisan migrate --force
docker compose exec backend php artisan config:cache
```
> Si entre medio se renovó el cert, los `cp` + `restart nginx` (deploy-hook) ya están hechos; recarga manual opcional:
> `sudo cp -L /etc/letsencrypt/live/api.vibeer.com/{fullchain.pem,privkey.pem} Docker/certs/real/ && docker compose restart nginx`

## 8. Backups / persistencia

- Postgres: **no hay volumen declarado** en `docker-compose.yml` → usa un volumen **anónimo** (fragilidad conocida: puede perderse con `up --force-recreate` o restart de WSL). **Recomendación:** declarar un volumen con nombre (p. ej. `pgdata`) en `docker-compose.yml` antes del deploy real. Verificar con `docker volume ls`.
- Dump manual (sin cron; cuando lo pidas):
  ```bash
  cd /var/www/backendMusic
  docker compose exec -T postgres pg_dump -U $DB_USERNAME $DB_DATABASE | gzip > ~/vibeer_$(date +%F).sql.gz
  ```
- La SPA (`spa/`) se regenera desde la imagen: no requiere backup.

## 9. Comandos manuales (sin cron / scheduler / workers)

```bash
docker compose exec backend php artisan events:send-reminders
docker compose exec backend php artisan events:send-completed-reminders
docker compose exec backend php artisan events:send-artist-hour-reminders
docker compose exec backend php artisan sanctions:lift-expired
docker compose exec backend php artisan approvals:expire
docker compose exec backend php artisan queue:work --once     # un job de cola
```

## 10. Dev local (recuerda)

- `docker compose --profile dev up -d` o tener `COMPOSE_PROFILES=dev` en `.env` para levantar MailHog (servicio con `profiles: ["dev"]`).
- En local los blocks 80/443 usan certs **self-signed** (`Docker/certs/real/`, gitignored); en el VPS los PEM reales de certbot.