# ─────────────────────────────────────────────────────────────────────────────
# Dockerfile — Taskly Laravel app
# Base: php:8.2-apache
# ─────────────────────────────────────────────────────────────────────────────

FROM php:8.2-apache

# ── System dependencies ───────────────────────────────────────────────────────
RUN apt-get update && apt-get install -y \
        git \
        curl \
        unzip \
        libpq-dev \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# ── PHP extensions ────────────────────────────────────────────────────────────
RUN docker-php-ext-install \
        pdo \
        pdo_pgsql \
        mbstring \
        xml \
        zip \
        bcmath \
        gd

# ── Composer ──────────────────────────────────────────────────────────────────
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ── Apache: point document root at /public and enable mod_rewrite ────────────
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/sites-available/000-default.conf \
        /etc/apache2/apache2.conf \
    && a2enmod rewrite

# ── Copy application files ────────────────────────────────────────────────────
WORKDIR /var/www/html

COPY . .

# ── Install PHP dependencies (production, no dev) ────────────────────────────
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# ── Storage & cache permissions ───────────────────────────────────────────────
RUN chown -R www-data:www-data /var/www/html/storage \
                                /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage \
                    /var/www/html/bootstrap/cache

# ── Apache .htaccess override ─────────────────────────────────────────────────
# Laravel's public/.htaccess requires AllowOverride All
RUN sed -i 's/AllowOverride None/AllowOverride All/g' \
        /etc/apache2/apache2.conf

EXPOSE 80

# ── Tell Apache to listen on port 10000 (required by Render free tier) ───────
RUN sed -i 's/Listen 80/Listen 10000/' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:10000>/' \
        /etc/apache2/sites-available/000-default.conf

EXPOSE 10000

CMD ["apache2-foreground"]
