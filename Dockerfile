# Stage 1: Build assets
FROM node:20-alpine AS assets
WORKDIR /app
COPY . .
RUN npm install && npm run build

# Stage 2: App
FROM php:8.3-fpm-alpine

# Instalar dependências de sistema
RUN apk add --no-cache \
    nginx \
    supervisor \
    postgresql-dev \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    ca-certificates

# Instalar extensões PHP
RUN docker-php-ext-install pdo_pgsql gd zip opcache

# Configurações de produção do PHP
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Configurações do Nginx
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Configurações do Supervisord
COPY docker/supervisord.conf /etc/supervisord.conf

# Preparar o diretório da aplicação
WORKDIR /var/www/html
COPY . .
COPY --from=assets /app/public/build ./public/build

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# Ajustar permissões
RUN chown -R www-data:www-data storage bootstrap/cache

# Script de entrada
COPY docker/entrypoint.sh /usr/local/bin/start-container
RUN chmod +x /usr/local/bin/start-container

EXPOSE 80

ENTRYPOINT ["start-container"]
