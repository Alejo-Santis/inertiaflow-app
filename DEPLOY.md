# InertiaFlow — Despliegue en AWS Free Tier con DuckDNS

> Stack: Ubuntu 24.04 · Nginx · PHP 8.3 · PostgreSQL 16 · Supervisor · Let's Encrypt

---

## Índice

1. [AWS — Crear la instancia EC2](#1-aws--crear-la-instancia-ec2)
2. [DuckDNS — Dominio gratuito](#2-duckdns--dominio-gratuito)
3. [Servidor — Instalar el stack](#3-servidor--instalar-el-stack)
4. [PostgreSQL — Base de datos](#4-postgresql--base-de-datos)
5. [Aplicación — Subir y configurar](#5-aplicación--subir-y-configurar)
6. [Nginx — Servidor web](#6-nginx--servidor-web)
7. [SSL — Certificado HTTPS gratis](#7-ssl--certificado-https-gratis)
8. [Supervisor — Worker de emails](#8-supervisor--worker-de-emails)
9. [Setup inicial de la app](#9-setup-inicial-de-la-app)
10. [Verificación final](#10-verificación-final)
11. [Actualizaciones y mantenimiento](#11-actualizaciones-y-mantenimiento)

---

## 1. AWS — Crear la instancia EC2

### 1.1 Lanzar la instancia

1. Abre [AWS Console → EC2](https://console.aws.amazon.com/ec2)
2. Click **"Launch Instance"**
3. Configura:

| Campo | Valor |
|-------|-------|
| Name | `inertiaflow-prod` |
| AMI | **Ubuntu Server 24.04 LTS** (Free tier eligible) |
| Instance type | **t2.micro** (Free tier: 750 h/mes gratis por 12 meses) |
| Key pair | Crea uno nuevo → descarga el `.pem` → guárdalo en un lugar seguro |
| Storage | **20 GB** gp3 (Free tier incluye 30 GB) |

4. En **Network settings → Edit** agrega estas reglas de entrada al Security Group:

| Type | Protocol | Port range | Source |
|------|----------|-----------|--------|
| SSH | TCP | 22 | My IP *(solo tu IP para mayor seguridad)* |
| HTTP | TCP | 80 | 0.0.0.0/0 |
| HTTPS | TCP | 443 | 0.0.0.0/0 |

5. Click **"Launch Instance"** y espera 1-2 minutos.

### 1.2 IP Elástica (permanente)

> Sin IP elástica, la IP pública cambia cada vez que reinicias el servidor.

1. EC2 → **Elastic IPs** → **Allocate Elastic IP address**
2. Selecciona la IP creada → **Actions → Associate Elastic IP address**
3. Selecciona tu instancia `inertiaflow-prod` → **Associate**
4. **Anota la IP.** Ejemplo: `54.123.45.67`

---

## 2. DuckDNS — Dominio gratuito

1. Entra a [duckdns.org](https://www.duckdns.org) con tu cuenta de Google o GitHub
2. Crea un subdominio. Ejemplo: `miempresa` → quedará `miempresa.duckdns.org`
3. En el campo **"current ip"** pega tu **Elastic IP** de AWS (`54.123.45.67`)
4. Click **"update ip"** → debe aparecer `OK` en verde

Verifica desde tu terminal local (espera 1-2 minutos):

```bash
ping miempresa.duckdns.org
# Debe resolver tu Elastic IP
```

---

## 3. Servidor — Instalar el stack

### 3.1 Conectarse por SSH

```bash
# Desde tu máquina local
chmod 400 tu-key.pem
ssh -i tu-key.pem ubuntu@54.123.45.67
```

### 3.2 Actualizar el sistema

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y curl git unzip zip
```

### 3.3 PHP 8.3 y extensiones requeridas

```bash
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

sudo apt install -y \
  php8.3 php8.3-fpm php8.3-cli \
  php8.3-pgsql php8.3-mbstring php8.3-xml \
  php8.3-curl php8.3-zip php8.3-bcmath \
  php8.3-tokenizer php8.3-intl php8.3-gd

php -v   # Debe mostrar PHP 8.3.x
```

### 3.4 Nginx

```bash
sudo apt install -y nginx
sudo systemctl enable nginx
sudo systemctl start nginx
```

### 3.5 Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer --version   # Debe mostrar 2.x
```

### 3.6 Node.js 22

```bash
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt install -y nodejs
node -v   # v22.x
npm -v    # 10.x
```

### 3.7 Supervisor (para el worker de emails)

```bash
sudo apt install -y supervisor
sudo systemctl enable supervisor
sudo systemctl start supervisor
```

---

## 4. PostgreSQL — Base de datos

### 4.1 Instalar

```bash
sudo apt install -y postgresql postgresql-contrib
sudo systemctl enable postgresql
sudo systemctl start postgresql
```

### 4.2 Crear usuario y base de datos

```bash
sudo -u postgres psql
```

Dentro de psql ejecuta *(cambia la contraseña)*:

```sql
CREATE USER inertiaflow WITH PASSWORD 'TuContraseñaSegura2024!';
CREATE DATABASE inertiaflow_db OWNER inertiaflow;
GRANT ALL PRIVILEGES ON DATABASE inertiaflow_db TO inertiaflow;
\q
```

---

## 5. Aplicación — Subir y configurar

### 5.1 Crear directorio

```bash
sudo mkdir -p /var/www/inertiaflow
sudo chown ubuntu:ubuntu /var/www/inertiaflow
```

### 5.2 Subir el código

**Desde tu máquina local** *(recomendado para la primera vez)*:

```bash
# Ejecuta esto en tu máquina local, NO en el servidor
cd /ruta/local/inertiaflow-app

rsync -avz \
  --exclude='node_modules' \
  --exclude='vendor' \
  --exclude='.git' \
  --exclude='.env' \
  -e "ssh -i tu-key.pem" \
  . ubuntu@54.123.45.67:/var/www/inertiaflow/
```

**Alternativa — clonar desde GitHub** *(si tienes el repo subido)*:

```bash
# En el servidor
git clone https://github.com/tu-usuario/inertiaflow.git /var/www/inertiaflow
```

### 5.3 Instalar dependencias en el servidor

```bash
cd /var/www/inertiaflow

# Dependencias PHP (sin paquetes de desarrollo)
composer install --no-dev --optimize-autoloader

# Dependencias JS y compilar assets
npm install
npm run build

# Liberar espacio eliminando node_modules
rm -rf node_modules
```

### 5.4 Configurar el entorno

```bash
nano /var/www/inertiaflow/.env
```

Pega y ajusta estos valores:

```ini
APP_NAME="InertiaFlow"
APP_ENV=production
APP_KEY=                              # Se genera en el siguiente paso
APP_DEBUG=false
APP_URL=https://miempresa.duckdns.org

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=inertiaflow_db
DB_USERNAME=inertiaflow
DB_PASSWORD=TuContraseñaSegura2024!

QUEUE_CONNECTION=database
SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database

# ── Email ──────────────────────────────────────────────────────
# Opciones gratuitas recomendadas:
#   Brevo (brevo.com)  → 300 emails/día gratis
#   Resend (resend.com) → 3.000 emails/mes gratis
#
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=tu@email.com
MAIL_PASSWORD=tu-api-key-brevo
MAIL_FROM_ADDRESS=noreply@miempresa.duckdns.org
MAIL_FROM_NAME="InertiaFlow"
```

### 5.5 Generar clave y preparar la base de datos

```bash
cd /var/www/inertiaflow

php artisan key:generate
php artisan migrate --force
php artisan storage:link

# Permisos de escritura para Nginx
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## 6. Nginx — Servidor web

### 6.1 Crear configuración del sitio

```bash
sudo nano /etc/nginx/sites-available/inertiaflow
```

Pega *(reemplaza `miempresa.duckdns.org` en todas las líneas)*:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name miempresa.duckdns.org;

    root /var/www/inertiaflow/public;
    index index.php;

    charset utf-8;

    # Headers de seguridad
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header Referrer-Policy "strict-origin-when-cross-origin";

    # Logs
    access_log /var/log/nginx/inertiaflow_access.log;
    error_log  /var/log/nginx/inertiaflow_error.log;

    # Tamaño máximo de archivos adjuntos
    client_max_body_size 20M;

    # Gzip para mejor rendimiento
    gzip on;
    gzip_types text/plain text/css application/json application/javascript
               text/xml application/xml image/svg+xml;

    # Laravel / Inertia — todas las rutas al index.php
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Assets Vite — caché agresivo (nombres con hash)
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 300;
    }

    # Bloquear archivos ocultos y .env
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 6.2 Activar el sitio

```bash
sudo ln -s /etc/nginx/sites-available/inertiaflow /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default   # quitar el sitio por defecto de nginx
sudo nginx -t                                  # verificar sintaxis — debe decir "ok"
sudo systemctl reload nginx
```

Prueba en el navegador: `http://miempresa.duckdns.org` — debe cargar la app (sin SSL aún).

---

## 7. SSL — Certificado HTTPS gratis

```bash
sudo apt install -y certbot python3-certbot-nginx

sudo certbot --nginx \
  -d miempresa.duckdns.org \
  --non-interactive \
  --agree-tos \
  --email tu@email.com \
  --redirect   # fuerza redirección HTTP → HTTPS automáticamente
```

Certbot modifica Nginx automáticamente y programa la renovación cada 90 días.

Verifica que la renovación automática funciona:

```bash
sudo certbot renew --dry-run
# Debe terminar con: "All simulated renewals succeeded"
```

Ahora `https://miempresa.duckdns.org` debe cargar con candado verde.

---

## 8. Supervisor — Worker de emails

Sin el worker, los emails de invitaciones y asignaciones de tareas quedan atascados en la cola y nunca se envían.

### 8.1 Crear configuración

```bash
sudo nano /etc/supervisor/conf.d/inertiaflow-worker.conf
```

```ini
[program:inertiaflow-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/inertiaflow/artisan queue:work --sleep=3 --tries=3 --max-time=3600 --timeout=120
directory=/var/www/inertiaflow
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/inertiaflow/storage/logs/worker.log
stopwaitsecs=3600
```

### 8.2 Activar y verificar

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start inertiaflow-worker:*
sudo supervisorctl status
# Debe mostrar: inertiaflow-worker:inertiaflow-worker_00   RUNNING
```

---

## 9. Setup inicial de la app

Ejecuta el asistente interactivo de configuración:

```bash
php artisan app:setup
```

El comando te guía paso a paso:

```
[ 1/4 ] Crea roles y permisos del sistema
[ 2/4 ] Pide nombre, email y contraseña del administrador
[ 3/4 ] Pide el nombre de la empresa / organización
         └── Opcionalmente crea el primer departamento
[ 4/4 ] Muestra resumen y próximos pasos
```

Al terminar verás algo así:

```
┌──────────────────┬─────────────────────────────────────┐
│ URL de la app    │ https://miempresa.duckdns.org        │
│ Administrador    │ Juan Pérez                           │
│ Email de acceso  │ juan@miempresa.com                   │
│ Organización     │ Mi Empresa S.A.                      │
└──────────────────┴─────────────────────────────────────┘
```

---

## 10. Verificación final

### Checklist antes de dar acceso al equipo

```bash
# Estado de todos los servicios
sudo systemctl status nginx php8.3-fpm postgresql supervisor
```

- [ ] `https://miempresa.duckdns.org` carga la pantalla de login con candado HTTPS
- [ ] Login con el email y contraseña del `app:setup` funciona
- [ ] `APP_DEBUG=false` en `.env` (nunca mostrar errores al usuario)
- [ ] Worker activo: `sudo supervisorctl status` muestra `RUNNING`
- [ ] Email configurado — prueba enviando una invitación de org desde la app
- [ ] `.env` inaccesible: visitar `https://miempresa.duckdns.org/.env` debe retornar 403

### Ver logs en tiempo real

```bash
# Errores de la aplicación
tail -f /var/www/inertiaflow/storage/logs/laravel.log

# Emails procesados por el worker
tail -f /var/www/inertiaflow/storage/logs/worker.log

# Errores de Nginx
sudo tail -f /var/log/nginx/inertiaflow_error.log
```

---

## 11. Actualizaciones y mantenimiento

### Desplegar una nueva versión

```bash
cd /var/www/inertiaflow

# 1. Modo mantenimiento (muestra página de "Volvemos pronto")
php artisan down

# 2. Traer cambios (si usas Git)
git pull origin main

# 3. Dependencias y assets
composer install --no-dev --optimize-autoloader
npm ci && npm run build && rm -rf node_modules

# 4. Migraciones pendientes
php artisan migrate --force

# 5. Regenerar cachés
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Reiniciar worker
sudo supervisorctl restart inertiaflow-worker:*

# 7. Permisos (por si acaso)
sudo chown -R www-data:www-data storage bootstrap/cache

# 8. Volver online
php artisan up
```

### Backup de la base de datos

```bash
# Crear backup
pg_dump -U inertiaflow -h 127.0.0.1 inertiaflow_db \
  > ~/backup_inertiaflow_$(date +%Y%m%d_%H%M).sql

# Restaurar desde backup
psql -U inertiaflow -h 127.0.0.1 inertiaflow_db < ~/backup_inertiaflow_20260415_1200.sql
```

### Referencia rápida de comandos

```bash
# Estado del worker de emails
sudo supervisorctl status

# Reiniciar servicios
sudo systemctl restart nginx
sudo systemctl restart php8.3-fpm
sudo supervisorctl restart inertiaflow-worker:*

# Modo mantenimiento
php artisan down    # activar
php artisan up      # desactivar

# Limpiar todo si algo falla
php artisan optimize:clear
```

---

## Costos en AWS Free Tier

| Recurso | Incluido gratis | Detalle |
|---------|----------------|---------|
| EC2 t2.micro | 750 h/mes × 12 meses | 1 instancia corriendo 24/7 |
| EBS 20 GB | 30 GB/mes | Almacenamiento incluido |
| Elastic IP | Gratis si está asociada | Se cobra si no tiene instancia |
| Transferencia de datos | 100 GB/mes salida | Más que suficiente para MVP |
| **Total año 1** | **~$0 USD/mes** | Solo pagas el dominio si no usas DuckDNS |
| Después de 12 meses | ~$8.50 USD/mes | t2.micro on-demand |

---

## Flujo completo del primer día

```
Admin entra con sus credenciales
  → Organizaciones → ya existe la empresa creada por app:setup
  → Invita al equipo por email (llega por la cola de Supervisor)
  → Cada miembro acepta → recibe acceso automáticamente
  → Admin crea proyectos asignados a la org
  → Equipo trabaja: tareas · kanban · reuniones · comentarios
  → Notificaciones in-app + emails de asignación en tiempo real
```
