# ==========================================
# ETAPA 1: Compilar assets de Vue (Frontend)
# ==========================================
FROM node:20-alpine AS node_builder
WORKDIR /app

# Copiar archivos de dependencias de Node
COPY package*.json ./
RUN npm ci

# Copiar el proyecto y compilar Vue/CSS
COPY . .
RUN npm run build

# ==========================================
# ETAPA 2: Entorno de Producción (Backend PHP)
# ==========================================
FROM php:8.2-fpm-alpine

# Instalar extensiones de PHP comunes y Nginx
RUN apk add --no-cache \
    nginx \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# Copiar Composer desde la imagen oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copiar código fuente
COPY . .

# Copiar los assets de Vue ya compilados en la ETAPA 1 a la carpeta public
COPY --from=node_builder /app/public/build ./public/build

# Instalar dependencias de PHP (sin entorno de desarrollo)
RUN composer install --no-dev --optimize-autoloader

# Configuración básica de Nginx para el monolito
RUN echo 'server { \
    listen 80; \
    index index.php index.html; \
    root /var/www/public; \
    location / { \
        try_files $uri $uri/ /index.php?$query_string; \
    } \
    location ~ \.php$ { \
        fastcgi_pass 127.0.0.1:9000; \
        fastcgi_index index.php; \
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; \
        include fastcgi_params; \
    } \
}' > /etc/nginx/http.d/default.conf

# Script de arranque para ejecutar Nginx y PHP-FPM juntos
EXPOSE 80
CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]
