# InertiaFlow — Guía de despliegue en producción

Esta guía cubre el despliegue completo en un servidor Ubuntu/Debian con **Nginx** o **Apache2**, PostgreSQL, SSL y cola de trabajos con Supervisor.

---

## Índice

1. [Requisitos del servidor](#1-requisitos-del-servidor)
2. [Preparar el servidor](#2-preparar-el-servidor)
3. [Configurar PostgreSQL](#3-configurar-postgresql)
4. [Clonar y configurar la aplicación](#4-clonar-y-configurar-la-aplicación)
5. [Configurar Nginx](#5-configurar-nginx)
6. [Configurar Apache2](#6-configurar-apache2)
7. [Certificado SSL con Let's Encrypt](#7-certificado-ssl-con-lets-encrypt)
8. [Queue worker con Supervisor](#8-queue-worker-con-supervisor)
9. [Permisos y caché final](#9-permisos-y-caché-final)
10. [Checklist de verificación](#10-checklist-de-verificación)
11. [Actualizaciones futuras](#11-actualizaciones-futuras)

---

## 1. Requisitos del servidor

- Ubuntu 22.04 LTS / Debian 12 (recomendado)
- RAM: mínimo 1 GB (2 GB recomendado)
- Disco: mínimo 10 GB libres
- Acceso root o sudo
- Puertos abiertos: 22 (SSH), 80 (HTTP), 443 (HTTPS)

---

## 2. Preparar el servidor

### Actualizar el sistema

```bash
sudo apt update && sudo apt upgrade -y
```

### Instalar PHP 8.3 y extensiones

```bash
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

sudo apt install -y \
  php8.3 php8.3-fpm php8.3-cli \
  php8.3-pgsql php8.3-mbstring php8.3-xml \
  php8.3-bcmath php8.3-ctype php8.3-tokenizer \
  php8.3-json php8.3-openssl php8.3-curl \
  php8.3-zip php8.3-intl
```

### Instalar Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

### Instalar Node.js 20.x

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
node --version && npm --version
```

### Instalar PostgreSQL

```bash
sudo apt install -y postgresql postgresql-contrib
sudo systemctl enable postgresql
sudo systemctl start postgresql
```

---

## 3. Configurar PostgreSQL

```bash
# Acceder como usuario postgres
sudo -u postgres psql

-- Dentro de psql:
CREATE USER inertiaflow WITH PASSWORD 'contraseña_muy_segura';
CREATE DATABASE inertiaflow_db OWNER inertiaflow;
GRANT ALL PRIVILEGES ON DATABASE inertiaflow_db TO inertiaflow;
\q
```

---

## 4. Clonar y configurar la aplicación

### Crear directorio y clonar

```bash
sudo mkdir -p /var/www/inertiaflow
sudo chown $USER:$USER /var/www/inertiaflow

git clone <url-del-repo> /var/www/inertiaflow
cd /var/www/inertiaflow
```

### Instalar dependencias

```bash
composer install --optimize-autoloader --no-dev
npm install
```

### Configurar el entorno

```bash
cp .env.example .env
php artisan key:generate
nano .env   # o vim .env
```

Ajustar como mínimo estas variables:

```env
APP_NAME=InertiaFlow
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tudominio.com
APP_LOCALE=es

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=inertiaflow_db
DB_USERNAME=inertiaflow
DB_PASSWORD=contraseña_muy_segura

SESSION_DRIVER=database
SESSION_ENCRYPT=true
SESSION_LIFETIME=120

QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.tuproveedor.com
MAIL_PORT=587
MAIL_USERNAME=usuario_smtp
MAIL_PASSWORD=contraseña_smtp
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tudominio.com
MAIL_FROM_NAME="InertiaFlow"
```

### Ejecutar migraciones y seeder

```bash
php artisan migrate --seed --force
```

### Compilar assets para producción

```bash
npm run build
```

### Optimizar Laravel

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### Permisos de almacenamiento

```bash
sudo chown -R www-data:www-data /var/www/inertiaflow/storage
sudo chown -R www-data:www-data /var/www/inertiaflow/bootstrap/cache
sudo chmod -R 775 /var/www/inertiaflow/storage
sudo chmod -R 775 /var/www/inertiaflow/bootstrap/cache
```

---

## 5. Configurar Nginx

### Instalar Nginx

```bash
sudo apt install -y nginx
sudo systemctl enable nginx
```

### Crear virtual host

```bash
sudo nano /etc/nginx/sites-available/inertiaflow
```

Pegar la siguiente configuración (reemplaza `tudominio.com`):

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name tudominio.com www.tudominio.com;

    # Redirigir todo a HTTPS (después de instalar SSL)
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name tudominio.com www.tudominio.com;

    root /var/www/inertiaflow/public;
    index index.php;

    # SSL — Certbot completará estas líneas automáticamente
    # ssl_certificate     /etc/letsencrypt/live/tudominio.com/fullchain.pem;
    # ssl_certificate_key /etc/letsencrypt/live/tudominio.com/privkey.pem;

    # Seguridad de headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";
    add_header Referrer-Policy "strict-origin-when-cross-origin";

    # Logs
    access_log /var/log/nginx/inertiaflow_access.log;
    error_log  /var/log/nginx/inertiaflow_error.log;

    # Tamaño máximo de subida
    client_max_body_size 50M;

    # Gzip
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml image/svg+xml;

    # Inertia/Laravel: todas las rutas al index.php
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Assets compilados por Vite — caché agresivo
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }

    # PHP-FPM
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    # Bloquear acceso a archivos ocultos
    location ~ /\. {
        deny all;
    }

    # Bloquear acceso directo a .env
    location ~ /\.env {
        deny all;
        return 404;
    }
}
```

### Activar y verificar

```bash
sudo ln -s /etc/nginx/sites-available/inertiaflow /etc/nginx/sites-enabled/
sudo nginx -t                    # verificar sintaxis
sudo systemctl reload nginx
```

---

## 6. Configurar Apache2

### Instalar Apache2 y módulos

```bash
sudo apt install -y apache2
sudo a2enmod rewrite headers ssl proxy_fcgi setenvif
sudo a2enconf php8.3-fpm
sudo systemctl enable apache2
```

### Crear virtual host

```bash
sudo nano /etc/apache2/sites-available/inertiaflow.conf
```

Pegar la siguiente configuración:

```apache
<VirtualHost *:80>
    ServerName tudominio.com
    ServerAlias www.tudominio.com

    # Redirigir todo a HTTPS (después de instalar SSL)
    Redirect permanent / https://tudominio.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName tudominio.com
    ServerAlias www.tudominio.com

    DocumentRoot /var/www/inertiaflow/public

    # SSL — Certbot completará estas líneas automáticamente
    # SSLEngine on
    # SSLCertificateFile    /etc/letsencrypt/live/tudominio.com/fullchain.pem
    # SSLCertificateKeyFile /etc/letsencrypt/live/tudominio.com/privkey.pem

    # Seguridad de headers
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"

    # Logs
    ErrorLog  ${APACHE_LOG_DIR}/inertiaflow_error.log
    CustomLog ${APACHE_LOG_DIR}/inertiaflow_access.log combined

    # Tamaño máximo de subida
    LimitRequestBody 52428800

    <Directory /var/www/inertiaflow/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # PHP-FPM via socket
    <FilesMatch \.php$>
        SetHandler "proxy:unix:/run/php/php8.3-fpm.sock|fcgi://localhost"
    </FilesMatch>

    # Caché de assets compilados por Vite
    <FilesMatch "\.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$">
        Header set Cache-Control "max-age=31536000, public, immutable"
    </FilesMatch>
</VirtualHost>
```

> **Nota:** el archivo `public/.htaccess` ya viene incluido en Laravel y gestiona el rewrite de rutas automáticamente. Asegúrate de que `AllowOverride All` esté habilitado.

### Activar y verificar

```bash
sudo a2ensite inertiaflow.conf
sudo apache2ctl configtest           # verificar sintaxis
sudo systemctl reload apache2
```

---

## 7. Certificado SSL con Let's Encrypt

```bash
sudo apt install -y certbot

# Para Nginx:
sudo apt install -y python3-certbot-nginx
sudo certbot --nginx -d tudominio.com -d www.tudominio.com

# Para Apache2:
sudo apt install -y python3-certbot-apache
sudo certbot --apache -d tudominio.com -d www.tudominio.com
```

Certbot configura automáticamente el bloque SSL y programa la renovación automática. Verificar:

```bash
sudo certbot renew --dry-run
```

---

## 8. Queue worker con Supervisor

La aplicación utiliza colas de base de datos para el envío de correos (recuperación de contraseña). Sin el worker, los correos no se enviarán.

### Instalar Supervisor

```bash
sudo apt install -y supervisor
sudo systemctl enable supervisor
```

### Crear configuración del worker

```bash
sudo nano /etc/supervisor/conf.d/inertiaflow-worker.conf
```

```ini
[program:inertiaflow-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/inertiaflow/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/inertiaflow-worker.log
stopwaitsecs=3600
```

### Activar el worker

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start inertiaflow-worker:*
sudo supervisorctl status
```

---

## 9. Permisos y caché final

```bash
cd /var/www/inertiaflow

# Permisos
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Enlace simbólico de storage (si usas uploads)
php artisan storage:link

# Optimización final
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

---

## 10. Checklist de verificación

Antes de dar acceso a los usuarios, verifica cada punto:

- [ ] `APP_DEBUG=false` en `.env`
- [ ] `APP_ENV=production` en `.env`
- [ ] `APP_URL` apunta al dominio real con `https://`
- [ ] Certificado SSL instalado y funcionando
- [ ] Correo real configurado (no Mailtrap sandbox) — probar con `php artisan tinker` → `Mail::raw('test', fn($m) => $m->to('tu@email.com')->subject('Test'))`
- [ ] Contraseñas del seeder cambiadas (`admin@example.com` y `demo@example.com`)
- [ ] Queue worker activo: `sudo supervisorctl status`
- [ ] `php artisan queue:work` puede procesar jobs: revisar `/var/log/inertiaflow-worker.log`
- [ ] Assets compilados con `npm run build` (no `npm run dev`)
- [ ] Cachés aplicadas: `php artisan optimize`
- [ ] Permisos de `storage/` y `bootstrap/cache/` son de `www-data`
- [ ] Acceso directo a `.env` bloqueado (probar `https://tudominio.com/.env` — debe retornar 403/404)
- [ ] Logs de Nginx/Apache sin errores: `sudo tail -f /var/log/nginx/inertiaflow_error.log`

---

## 11. Actualizaciones futuras

Cada vez que despliegues una nueva versión:

```bash
cd /var/www/inertiaflow

# 1. Activar modo mantenimiento
php artisan down

# 2. Traer cambios
git pull origin main

# 3. Actualizar dependencias
composer install --optimize-autoloader --no-dev
npm install

# 4. Migraciones pendientes
php artisan migrate --force

# 5. Recompilar assets
npm run build

# 6. Limpiar y regenerar cachés
php artisan optimize:clear
php artisan optimize

# 7. Reiniciar queue worker
sudo supervisorctl restart inertiaflow-worker:*

# 8. Volver a producción
php artisan up
```

---

## Referencia rápida de comandos

```bash
# Estado del worker
sudo supervisorctl status inertiaflow-worker:*

# Logs de la app
tail -f /var/www/inertiaflow/storage/logs/laravel.log

# Logs del servidor web
tail -f /var/log/nginx/inertiaflow_error.log      # Nginx
tail -f /var/log/apache2/inertiaflow_error.log    # Apache2

# Reiniciar PHP-FPM
sudo systemctl restart php8.3-fpm

# Modo mantenimiento
php artisan down    # activar
php artisan up      # desactivar
```
