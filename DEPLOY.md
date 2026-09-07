# Despliegue en el VPS

Cada tienda vive en su propio subdominio:

```
https://miotienda.com              landing + login + superadmin (landing/ + panel/)
https://rex.miotienda.com          catálogo de la tienda "rex"  (web/  — Nuxt SSR)
https://rex.miotienda.com/admin    panel de esa tienda          (panel/ — Vue SPA)
https://api.miotienda.com          API                          (api/  — Laravel)
```

El único que necesita un proceso corriendo es Nuxt; los otros dos son PHP-FPM y
archivos estáticos.

> El slug de la tienda **es su subdominio**. `api`, `www`, `mail`, `panel` y
> algunos más están reservados: ninguna tienda puede llamarse así. La lista está
> en `api/config/catalog.php`.

---

## El VPS no es nuestro solo

Es un KVM de Hostinger con **cuatro sitios en producción encima**
(`youdenti.diseprog.com`, `diseprog.com`, `imaximagen.com`,
`clinicadentalorthoclinic.com`) y está administrado con **CloudPanel**.

Consecuencias, todas importantes:

- **El código no va en `/var/www`** sino en `/home/{site-user}/htdocs/{dominio}`.
  Cada sitio tiene su propio usuario de sistema y su `htdocs` en 770: un sitio
  no lee la carpeta del otro.
- **Los vhosts los genera CloudPanel.** No se copian archivos a
  `sites-available`: se editan desde el panel, y lo que se edita a mano se
  pierde cuando CloudPanel regenera el vhost.
- **nginx corre como `root`** (así lo deja CloudPanel), así que lee cualquier
  `htdocs` sin tocar grupos ni ACLs.
- **No se reinstala nada global** sin pensarlo dos veces: versiones de PHP,
  `my.cnf`, firewall, límites de nginx. Hay cuatro sitios que dependen de eso.
- **PHP de la línea de comandos es 8.4**, pero el sitio de la API corre con
  **8.3**. Todo comando de Laravel se ejecuta con `php8.3` explícito, o se
  instalan dependencias contra una versión distinta de la que sirve el sitio.
- **PM2 no se usa.** Existe uno instalado dentro del home de YouDenti, para lo
  suyo. El catálogo se levanta con **systemd**.

## Lo que ya está hecho

No hace falta rehacerlo:

| Qué | Estado |
|---|---|
| Los tres sitios en CloudPanel | creados, con SSL funcionando |
| DNS en Cloudflare | registros A para `@`, `*`, `www` y `api`, todos proxeados |
| SSL | certificado de origen de Cloudflare (`*.miotienda.com` + apex), vence 2041 |
| Modo de la zona | Full (strict) + Always Use HTTPS |

| Sitio | Tipo | Site user | Carpeta |
|---|---|---|---|
| `api.miotienda.com` | PHP 8.3 | `miotienda-api` | `/home/miotienda-api/htdocs/api.miotienda.com` |
| `miotienda.com` (+ `www`) | Static | `miotienda-apex` | `/home/miotienda-apex/htdocs/miotienda.com` |
| `*.miotienda.com` | Node.js | `miotienda-tiendas` | `/home/miotienda-tiendas/htdocs/tiendas.miotienda.com` |

> **Nada de certbot.** El certificado es de Cloudflare y dura 15 años. Y el
> tercer sitio se creó como `tiendas.miotienda.com` con el `server_name` editado
> a mano a `*.miotienda.com`: **reinstalar un certificado en ese sitio regenera
> el vhost y borra esa edición.**

---

## 1. Base de datos

Desde CloudPanel → **Databases → Add Database**. No usar `root` ni la base de
otro sitio.

```
Nombre    base_catalogos
Usuario   catalogos
```

CloudPanel genera la contraseña; se copia al `.env` de la API.

## 2. Clonar el repo — tres veces

El repo es uno solo y trae las tres aplicaciones, pero cada sitio de CloudPanel
tiene su propio usuario y no puede leer la carpeta del otro. Así que se clona
**entero en cada sitio** y cada uno usa la carpeta que le toca. Ocupa unos MB de
más y evita pelear contra el aislamiento por usuario.

**Clonar como el site user, nunca como root.** Si los archivos quedan de
`root:root`, PHP-FPM no puede escribir en `storage/` y el build de Node tampoco
puede escribir su salida.

```bash
sudo -u miotienda-api      git clone https://github.com/RALlave/catalogos.git \
    /home/miotienda-api/htdocs/api.miotienda.com

sudo -u miotienda-apex     git clone https://github.com/RALlave/catalogos.git \
    /home/miotienda-apex/htdocs/miotienda.com

sudo -u miotienda-tiendas  git clone https://github.com/RALlave/catalogos.git \
    /home/miotienda-tiendas/htdocs/tiendas.miotienda.com
```

Las tres carpetas están casi vacías (un `public/` vacío en la API, un
`index.html` de prueba en el apex). Si `git clone` se queja de que el destino no
está vacío, se borra lo que hay: es la plantilla de CloudPanel, no hace falta.

`prototipo-3/` es la maqueta estática de referencia: sobra en el servidor, pero
no molesta.

## 3. API (Laravel)

```bash
cd /home/miotienda-api/htdocs/api.miotienda.com/api

sudo -u miotienda-api composer install --no-dev --optimize-autoloader

sudo -u miotienda-api cp ../deploy/env.api.production.example .env
sudo -u miotienda-api nano .env        # DB_PASSWORD, SMTP, APP_URL, FRONTEND_URL

sudo -u miotienda-api php8.3 artisan key:generate
sudo -u miotienda-api php8.3 artisan migrate --force
sudo -u miotienda-api php8.3 artisan db:seed --class=RoleSeeder --force
sudo -u miotienda-api php8.3 artisan storage:link   # SIN ESTO NO SE VEN LAS IMÁGENES

sudo -u miotienda-api php8.3 artisan config:cache
sudo -u miotienda-api php8.3 artisan route:cache
sudo -u miotienda-api php8.3 artisan view:cache

# El superadmin se crea acá, no en el .env
sudo -u miotienda-api php8.3 artisan superadmin:create
```

Y el root del sitio, que por defecto apunta al `public/` de la plantilla de
CloudPanel: **CloudPanel → Sites → api.miotienda.com → Settings → Root
Directory**

```
htdocs/api.miotienda.com/api/public
```

> `APP_URL` es el subdominio pelado (`https://api.miotienda.com`), **sin
> `/api`**. Las rutas de la API cuelgan de ese prefijo, pero las imágenes no: el
> disco público arma sus URLs como `APP_URL + /storage`. Con `/api` al final no
> carga ninguna foto.

> `FRONTEND_URL` es el dominio pelado (`https://miotienda.com`), sin ruta y sin
> tienda. De ahí salen tres cosas: la dirección del catálogo de cada tienda, el
> enlace del correo de "recuperar contraseña" y **el patrón de CORS**. Si está
> mal, el panel no puede hablar con la API y el error aparece recién en el
> navegador.

> `CACHE_STORE` no puede quedar en `array`: el código con el que una sesión
> salta de un subdominio a otro se guarda en la caché. Con `array` se pierde
> entre una petición y la siguiente, y entrar al panel de una tienda falla
> siempre. El `.env` de ejemplo ya trae `database`.

Permisos: PHP necesita escribir en dos carpetas y en ninguna otra. Si se clonó
como el site user ya están bien; esto es por las dudas.

```bash
sudo chown -R miotienda-api:miotienda-api storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

## 4. Catálogo público (Nuxt)

```bash
cd /home/miotienda-tiendas/htdocs/tiendas.miotienda.com/web

sudo -u miotienda-tiendas cp ../deploy/env.web.production.example .env
sudo -u miotienda-tiendas npm ci
sudo -u miotienda-tiendas npm run build        # deja el resultado en .output/
```

## 5. Panel (Vue) — se compila dos veces

El panel se sirve desde **dos sitios**: el apex (`/login`, `/registro`,
`/superadmin`) y el subdominio de cada tienda (`/admin`). Cada uno compila el
suyo, en su propia carpeta.

```bash
cd /home/miotienda-apex/htdocs/miotienda.com/panel
sudo -u miotienda-apex cp ../deploy/env.panel.production.example .env
sudo -u miotienda-apex npm ci
sudo -u miotienda-apex npm run build

cd /home/miotienda-tiendas/htdocs/tiendas.miotienda.com/panel
sudo -u miotienda-tiendas cp ../deploy/env.panel.production.example .env
sudo -u miotienda-tiendas npm ci
sudo -u miotienda-tiendas npm run build
```

> `VITE_BASE=/panel/` tiene que estar **antes** del build. Es de dónde cuelgan
> los archivos, no las rutas: sin esa variable el panel pide su JavaScript a la
> raíz, contesta Nuxt con el catálogo y la SPA queda en blanco.

## 6. Nginx

### 6.1 La IP real del visitante

```bash
sudo cp /home/miotienda-api/htdocs/api.miotienda.com/deploy/cloudflare-realip.conf \
    /etc/nginx/snippets/

sudo nano /etc/nginx/global_settings      # agregar al final:
#   include /etc/nginx/snippets/cloudflare-realip.conf;

sudo nginx -t && sudo systemctl reload nginx
```

Va en `global_settings` porque CloudPanel ya lo incluye en el server block de
los cinco sitios. **Sin esto, `$remote_addr` es una IP de Cloudflare**: todas
las visitas de todas las tiendas se cuentan como un solo visitante.

Es el único cambio que toca a los otros cuatro sitios, y sólo para bien: pasan a
loguear la IP real. `set_real_ip_from` únicamente confía si la conexión viene de
un rango de Cloudflare, así que un sitio sin proxy naranja no cambia en nada.

### 6.2 Los bloques de cada sitio

En **CloudPanel → Sites → {sitio} → Vhost**, dentro del server block de 443:

- `deploy/vhost-apex.conf` → en `miotienda.com`
- `deploy/vhost-tiendas.conf` → en `tiendas.miotienda.com`

Los dos archivos traen adentro qué pegar y dónde. En el vhost de la API no hay
nada que agregar; sólo cambiar, ahí y en el del wildcard, la línea

```nginx
proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
```

por

```nginx
proxy_set_header X-Forwarded-For $remote_addr;
```

`StatService` cuenta la visita leyendo el primer valor de `X-Forwarded-For`. Con
`$proxy_add_x_forwarded_for` nginx antepone lo que mandó el cliente, y ese dato
lo escribe el visitante: cualquiera podría inflar las estadísticas de una tienda
ajena. Con `$remote_addr` viaja la IP que resolvió `real_ip`.

Verificar de paso que el límite de subida alcanza (las imágenes son de hasta
4 MB):

```bash
grep -rn "client_max_body_size" /etc/nginx/nginx.conf /etc/nginx/global_settings
```

## 7. Levantar Nuxt con systemd

```bash
sudo cp /home/miotienda-tiendas/htdocs/tiendas.miotienda.com/deploy/catalogos-web.service \
    /etc/systemd/system/

sudo systemctl daemon-reload
sudo systemctl enable --now catalogos-web

systemctl status catalogos-web
journalctl -u catalogos-web -f
```

Corre como `miotienda-tiendas`, no como root, con un techo de 500 MB de RAM
para que no pueda arrastrar a los otros sitios. Antes de arrancarlo conviene
mirar cuánta memoria hay libre:

```bash
free -m
```

---

## Comprobar que quedó bien

```bash
curl -I https://api.miotienda.com/api/themes    # 200
curl -I https://miotienda.com/login             # 200
curl -I https://rex.miotienda.com               # 200  (rex = una tienda real)
curl -I https://rex.miotienda.com/admin         # 200
systemctl status catalogos-web                  # active (running)
```

Y en el navegador, tres cosas que sólo se ven probándolas:

1. Entrar al panel y **abrir un producto con foto**. Si la imagen no carga, el
   problema está en `APP_URL` o falta el `storage:link`.
2. Loguearse en `miotienda.com/login` con un dueño de tienda: tiene que
   **saltar solo** a `su-tienda.miotienda.com/admin`, ya logueado.
3. Como superadmin, entrar al panel de una tienda desde el listado: también es
   un salto de dominio. Si vuelve al login, la caché está en `array` o el
   código venció.

Y que los otros cuatro sitios sigan en pie, que comparten el server:

```bash
curl -I https://youdenti.diseprog.com
free -m
```

---

## Actualizar después de un cambio

Son tres clones, así que son tres `git pull`. Cada sitio sólo reconstruye lo
suyo.

```bash
# API
cd /home/miotienda-api/htdocs/api.miotienda.com
sudo -u miotienda-api git pull
cd api
sudo -u miotienda-api composer install --no-dev --optimize-autoloader
sudo -u miotienda-api php8.3 artisan migrate --force
sudo -u miotienda-api php8.3 artisan config:cache
sudo -u miotienda-api php8.3 artisan route:cache

# Catálogo + panel de las tiendas
cd /home/miotienda-tiendas/htdocs/tiendas.miotienda.com
sudo -u miotienda-tiendas git pull
cd web   && sudo -u miotienda-tiendas npm ci && sudo -u miotienda-tiendas npm run build
cd ../panel && sudo -u miotienda-tiendas npm ci && sudo -u miotienda-tiendas npm run build
sudo systemctl restart catalogos-web

# Landing + panel del apex
cd /home/miotienda-apex/htdocs/miotienda.com
sudo -u miotienda-apex git pull
cd panel && sudo -u miotienda-apex npm ci && sudo -u miotienda-apex npm run build
```

Nginx no se reinicia: el panel y la landing son archivos, y el catálogo lo
reinicia systemd.

---

## Cosas que suelen fallar

| Síntoma | Causa |
|---|---|
| Panel en blanco | Se compiló sin `VITE_BASE=/panel/` |
| El panel no puede hablar con la API (error de CORS) | `FRONTEND_URL` no es el dominio pelado |
| Entrar al panel de una tienda devuelve al login | `CACHE_STORE=array`: el código de salto se pierde |
| No se ven las fotos | Falta `storage:link`, o `APP_URL` lleva `/api` al final |
| El catálogo da 502 | Nuxt no está corriendo: `systemctl status catalogos-web` |
| El catálogo daba 200 y de golpe da 404 en todas las tiendas | CloudPanel regeneró el vhost y se llevó el `server_name *.miotienda.com` |
| El panel dejó de abrir en `/admin` o `/login` | Lo mismo: el vhost regenerado perdió los bloques de `deploy/vhost-*.conf` |
| Todas las visitas se cuentan como una sola | Falta el `include` del snippet de Cloudflare en `global_settings` |
| Las estadísticas de una tienda tienen números absurdos | El vhost quedó con `$proxy_add_x_forwarded_for` |
| Una tienda nueva da 404 o error de certificado | Falta el registro DNS `*` proxeado |
| Bucle de redirecciones | Cloudflare en *Flexible* en vez de *Full (strict)* |
| `php artisan` se queja de una extensión o de la versión | Se corrió con `php` (8.4) en vez de `php8.3` |
| PHP no puede escribir en `storage/` | Se clonó como root en vez de como el site user |
| 419 o error de sesión | Falta `php8.3 artisan key:generate` |
| No llega el correo de recuperación | `MAIL_MAILER` sigue en `log` |
| "The selected slug is invalid" | El nombre choca con un subdominio reservado |
