# =========================================================
# ETAPA 1: Compilar frontend (Vue + Vite)
# =========================================================
FROM node:22-alpine AS frontend

WORKDIR /app

# Copiar archivos de npm
COPY package*.json ./

# Si existe package-lock.json, instala exactamente esas versiones
RUN if [ -f package-lock.json ]; then npm ci; else npm install; fi

# Copiar archivos necesarios para Vite
COPY resources ./resources
COPY public ./public
COPY vite.config.js ./

# Compilar Vue/Vite
RUN npm run build


# =========================================================
# ETAPA 2: Laravel + Apache
# =========================================================
FROM php:8.2-apache

# Dependencias PHP
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        zip \
        mbstring \
        exif \
        pcntl \
        bcmath \
        xml \
        intl \
        gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Apache
RUN a2enmod rewrite headers

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# =========================================================
# Composer
# =========================================================

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-scripts

# =========================================================
# Copiar Laravel
# =========================================================

COPY . .

# =========================================================
# Copiar frontend compilado desde Node
# =========================================================

COPY --from=frontend /app/public/build ./public/build

# =========================================================
# Autoload Laravel
# =========================================================

RUN composer dump-autoload --optimize

# =========================================================
# Apache -> public/
# =========================================================

RUN sed -i \
    's!/var/www/html!/var/www/html/public!g' \
    /etc/apache2/sites-available/000-default.conf

RUN echo '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' >> /etc/apache2/apache2.conf

# =========================================================
# Permisos Laravel
# =========================================================

RUN mkdir -p \
    storage/logs \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

# =========================================================
# Puerto
# =========================================================

EXPOSE 80

# =========================================================
# Iniciar Laravel
# =========================================================

CMD php artisan config:cache && \
    php artisan route:cache && \
    php artisan migrate --force && \
    php artisan db:seed --class=RolesAndPermissionsSeeder --force && \
    apache2-foreground
