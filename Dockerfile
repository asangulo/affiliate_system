FROM php:8.2-apache

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

RUN a2enmod rewrite headers

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-scripts

COPY . .

RUN composer dump-autoload --optimize

RUN sed -i \
    's!/var/www/html!/var/www/html/public!g' \
    /etc/apache2/sites-available/000-default.conf

RUN echo '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' >> /etc/apache2/apache2.conf

# Permisos y directorios de Laravel
RUN mkdir -p \
    storage/logs \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache
    
EXPOSE 80

CMD php artisan config:cache && \
    php artisan route:cache && \
    php artisan migrate --force && \
    php artisan db:seed --class=RolesAndPermissionsSeeder --force && \
    apache2-foreground