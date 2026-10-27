# 1. Declarar la imagen de composer como una etapa con un nombre
FROM composer:latest AS composer_stage

# ==========================================
# ETAPA 1: Compilar assets de Vue (Frontend)
# ==========================================
FROM node:24-alpine AS node_builder
WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY . .
RUN npm run build

# ==========================================
# ETAPA 2: Entorno de Producción (Backend PHP)
# ==========================================
FROM php:8.2-fpm-alpine

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

# 2. Ahora apuntas a la etapa declarada 'composer_stage'
COPY --from=composer_stage /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

COPY --from=node_builder /app/public/build ./public/build

RUN composer install --no-dev --optimize-autoloader

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

EXPOSE 80
CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]
