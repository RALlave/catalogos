# Despliegue en el VPS

Cada tienda vive en su propio subdominio:

```
https://miotienda.com              landing + login del superadmin (landing/ + panel/)
https://rex.miotienda.com          catálogo de la tienda "rex"    (web/  — Nuxt SSR)
https://rex.miotienda.com/admin    panel de esa tienda            (panel/ — Vue SPA)
https://api.miotienda.com          API                            (api/  — Laravel)
```

El único que necesita un proceso corriendo es Nuxt; los otros dos son PHP-FPM y
archivos estáticos.

> El slug de la tienda **es su subdominio**. `api`, `www`, `mail`, `panel` y
> algunos más están reservados: ninguna tienda puede llamarse así. La lista está
> en `api/config/catalog.php`.

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

## 4. DNS en Cloudflare

Cuatro registros, **todos proxeados** (nube naranja):

| Tipo | Nombre | Contenido |
|---|---|---|
| A | `@` | IP del VPS |
| A | `*` | IP del VPS |
| A | `www` | IP del VPS |
| A | `api` | IP del VPS |

El wildcard `*` es lo que hace que una tienda nueva funcione sin tocar nada.

## 5. API (Laravel)

```bash
cd /var/www/catalogos/api

composer install --no-dev --optimize-autoloader

cp ../deploy/env.api.production.example .env
nano .env                      # completar DB_PASSWORD, SMTP, APP_URL y FRONTEND_URL

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

> `FRONTEND_URL` es el dominio pelado (`https://miotienda.com`), sin ruta y sin
> tienda. De ahí salen tres cosas: la dirección del catálogo de cada tienda, el
> enlace del correo de recuperación y **el patrón de CORS**. Si está mal, el
> panel no puede hablar con la API y el error aparece recién en el navegador.

> `CACHE_STORE` no puede quedar en `array`: el código con el que una sesión
> salta de un subdominio a otro se guarda en la caché. Con `array` se pierde
> entre una petición y la siguiente, y entrar al panel de una tienda falla
> siempre. El `.env` de ejemplo ya trae `database`.

Permisos: PHP necesita escribir en dos carpetas y en ninguna otra.

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

## 6. Catálogo público (Nuxt)

```bash
cd /var/www/catalogos/web

cp ../deploy/env.web.production.example .env
npm ci
npm run build                  # deja el resultado en .output/
```

## 7. Panel (Vue)

```bash
cd /var/www/catalogos/panel

cp ../deploy/env.panel.production.example .env
npm ci
npm run build                  # deja el resultado en dist/
```

> `VITE_BASE=/panel/` tiene que estar **antes** del build. Es de dónde cuelgan
> los archivos, no las rutas: sin esa variable el panel pide su JavaScript a la
> raíz, Nuxt responde el catálogo y la SPA queda en blanco.

## 8. Nginx

```bash
sudo cp /var/www/catalogos/deploy/nginx.conf /etc/nginx/sites-available/miotienda.com
sudo nano /etc/nginx/sites-available/miotienda.com     # cambiar los tres server_name
sudo ln -s /etc/nginx/sites-available/miotienda.com /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default

sudo nginx -t && sudo systemctl reload nginx
```

El archivo trae arriba un bloque `set_real_ip_from` con los rangos de
Cloudflare. **No borrarlo**: sin eso, la IP que ve la API es la de Cloudflare y
la primera línea de `X-Forwarded-For` pasa a ser un dato que escribe el
visitante, con lo que cualquiera podría inflar las estadísticas de una tienda
ajena. La lista se actualiza en <https://www.cloudflare.com/ips/>.

## 9. Levantar Nuxt con PM2

```bash
cd /var/www/catalogos
pm2 start deploy/ecosystem.config.cjs
pm2 save
pm2 startup                    # copiar y ejecutar el comando que imprime
```

## 10. SSL

**No se usa certbot.** El dominio está en Cloudflare, que emite el wildcard
gratis y sin renovación. En el panel de Cloudflare:

1. **SSL/TLS → Overview**: modo **Full (strict)**. En *Flexible* el tramo hasta
   el VPS viaja en claro y aparecen bucles de redirección.
2. **SSL/TLS → Origin Server → Create Certificate**: cubrir `miotienda.com` y
   `*.miotienda.com`. Guardar el certificado y la clave en el VPS:

```bash
sudo mkdir -p /etc/ssl/cloudflare
sudo nano /etc/ssl/cloudflare/miotienda.pem      # pegar el certificado
sudo nano /etc/ssl/cloudflare/miotienda.key      # pegar la clave privada
sudo chmod 600 /etc/ssl/cloudflare/miotienda.key
```

3. Agregar a cada uno de los tres server blocks:

```nginx
listen 443 ssl;
listen [::]:443 ssl;

ssl_certificate     /etc/ssl/cloudflare/miotienda.pem;
ssl_certificate_key /etc/ssl/cloudflare/miotienda.key;
```

El Origin Certificate dura 15 años y **no es públicamente confiable**: vale
sólo para el tramo Cloudflare↔VPS. Entrar por la IP del servidor va a dar error
de certificado, y está bien que así sea.

Revisar también que Cloudflare no cachee `/login`, `/admin` ni
`api.miotienda.com`. Por defecto no lo hace, pero una Page Rule demasiado
amplia serviría el panel de una tienda a otra.

---

## Comprobar que quedó bien

```bash
curl -I https://api.miotienda.com/api/themes    # 200
curl -I https://miotienda.com/login             # 200
curl -I https://rex.miotienda.com               # 200  (rex = una tienda real)
curl -I https://rex.miotienda.com/admin         # 200
pm2 status                                      # catalogos-web · online
```

Y en el navegador, tres cosas que sólo se ven probándolas:

1. Entrar al panel e **abrir un producto con foto**. Si la imagen no carga, el
   problema está en `APP_URL` o falta el `storage:link`.
2. Loguearse en `miotienda.com/login` con un dueño de tienda: tiene que
   **saltar solo** a `su-tienda.miotienda.com/admin`, ya logueado.
3. Como superadmin, entrar al panel de una tienda desde el listado: también es
   un salto de dominio. Si vuelve al login, la caché está en `array` o el
   código venció.

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
| El panel no puede hablar con la API (error de CORS) | `FRONTEND_URL` no es el dominio pelado |
| Entrar al panel de una tienda devuelve al login | `CACHE_STORE=array`: el código de salto se pierde |
| Una tienda nueva da 404 o error de certificado | Falta el registro DNS `*` proxeado |
| Todas las visitas se cuentan igual, o el número es absurdo | Falta el bloque `real_ip` de Cloudflare |
| No se ven las fotos | Falta `storage:link`, o `APP_URL` no termina en `/api` |
| El catálogo da 502 | Nuxt no está corriendo: `pm2 status` y `pm2 logs` |
| Bucle de redirecciones | Cloudflare en *Flexible* en vez de *Full (strict)* |
| 419 o error de sesión | Falta `php artisan key:generate` |
| No llega el correo de recuperación | `MAIL_MAILER` sigue en `log` |
| "The selected slug is invalid" | El nombre choca con un subdominio reservado |
