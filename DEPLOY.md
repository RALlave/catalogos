# Despliegue en el VPS

Las tres aplicaciones van bajo un solo dominio:

```
https://diseprog.com/mitienda1     catálogo público   (web/  — Nuxt SSR)
https://diseprog.com/panel         panel y superadmin (panel/ — Vue SPA)
https://diseprog.com/api           API                (api/  — Laravel)
```

Un solo certificado SSL. El único que necesita un proceso corriendo es Nuxt;
los otros dos son PHP-FPM y archivos estáticos.

> Los slugs `api`, `panel`, `storage` y algunos más están reservados: ninguna
> tienda puede llamarse así. La lista está en `api/config/catalog.php`.

---

## 1. Preparar el VPS

```bash
sudo apt update && sudo apt upgrade -y

# PHP 8.3 y extensiones que pide Laravel
sudo apt install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring \
    php8.3-xml php8.3-curl php8.3-zip php8.3-gd php8.3-bcmath php8.3-intl

sudo apt install -y nginx mysql-server git unzip

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Node 20 + PM2
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
sudo npm install -g pm2
```

## 2. Base de datos

No usar `root`. Crear un usuario propio:

```sql
CREATE DATABASE base_catalogos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'catalogos'@'localhost' IDENTIFIED BY 'una-contraseña-larga';
GRANT ALL PRIVILEGES ON base_catalogos.* TO 'catalogos'@'localhost';
FLUSH PRIVILEGES;
```

## 3. Subir el código

```bash
sudo mkdir -p /var/www/catalogos
sudo chown -R $USER:www-data /var/www/catalogos

cd /var/www/catalogos
git clone <tu-repo> .
```

`prototipo-3/` es la maqueta estática de referencia: no hace falta en el servidor.

## 4. API (Laravel)

```bash
cd /var/www/catalogos/api

composer install --no-dev --optimize-autoloader

cp ../deploy/env.api.production.example .env
nano .env                      # completar DB_PASSWORD, SMTP y APP_URL

php artisan key:generate
php artisan migrate --force
php artisan db:seed --class=RoleSeeder --force
php artisan storage:link       # SIN ESTO NO SE VEN LAS IMÁGENES

php artisan config:cache
php artisan route:cache
php artisan view:cache

# El superadmin se crea acá, no en el .env
php artisan superadmin:create
```

Permisos: PHP necesita escribir en dos carpetas y en ninguna otra.

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

## 5. Catálogo público (Nuxt)

```bash
cd /var/www/catalogos/web

cp ../deploy/env.web.production.example .env
npm ci
npm run build                  # deja el resultado en .output/
```

## 6. Panel (Vue)

```bash
cd /var/www/catalogos/panel

cp ../deploy/env.panel.production.example .env
npm ci
npm run build                  # deja el resultado en dist/
```

> `VITE_BASE=/panel/` tiene que estar **antes** del build. Si compilás sin esa
> variable, el panel busca sus archivos en la raíz y queda en blanco.

## 7. Nginx

```bash
sudo cp /var/www/catalogos/deploy/nginx.conf /etc/nginx/sites-available/diseprog.com
sudo ln -s /etc/nginx/sites-available/diseprog.com /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default

sudo nginx -t && sudo systemctl reload nginx
```

## 8. Levantar Nuxt con PM2

```bash
cd /var/www/catalogos
pm2 start deploy/ecosystem.config.cjs
pm2 save
pm2 startup                    # copiar y ejecutar el comando que imprime
```

## 9. SSL

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d diseprog.com -d www.diseprog.com
```

Certbot edita el nginx.conf y deja la renovación automática.

---

## Comprobar que quedó bien

```bash
curl -I https://diseprog.com/api/themes        # 200
curl -I https://diseprog.com/panel             # 200
curl -I https://diseprog.com/mitienda1         # 200
pm2 status                                     # catalogos-web · online
```

Y en el navegador: entrar al panel, iniciar sesión y **abrir un producto con
foto**. Si la imagen no carga, el problema está en `APP_URL` o falta el
`storage:link`.

---

## Actualizar después de un cambio

```bash
cd /var/www/catalogos && git pull

cd api    && composer install --no-dev --optimize-autoloader \
          && php artisan migrate --force \
          && php artisan config:cache && php artisan route:cache

cd ../web   && npm ci && npm run build && pm2 restart catalogos-web
cd ../panel && npm ci && npm run build
```

El panel y el catálogo no necesitan reiniciar nginx: uno son archivos y el
otro lo reinicia PM2.

---

## Cosas que suelen fallar

| Síntoma | Causa |
|---|---|
| Panel en blanco | Se compiló sin `VITE_BASE=/panel/` |
| No se ven las fotos | Falta `storage:link`, o `APP_URL` no termina en `/api` |
| El catálogo da 502 | Nuxt no está corriendo: `pm2 status` y `pm2 logs` |
| 419 o error de sesión | Falta `php artisan key:generate` |
| No llega el correo de recuperación | `MAIL_MAILER` sigue en `log` |
| "The selected slug is invalid" | El nombre choca con un slug reservado |
