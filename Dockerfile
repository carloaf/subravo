# =============================================================================
# Multi-Architecture Dockerfile for HelpSub
# Supports: linux/amd64, linux/arm64
# =============================================================================

ARG BUILDPLATFORM
ARG TARGETPLATFORM

# -----------------------------------------------------------------------------
# Stage 1: Frontend Build (Node.js)
# -----------------------------------------------------------------------------
FROM node:20-alpine AS frontend
LABEL stage="frontend-build"
LABEL description="Build frontend assets with Vite"

WORKDIR /app

ARG TARGETPLATFORM
ARG BUILDPLATFORM
RUN echo "Building frontend for TARGETPLATFORM=${TARGETPLATFORM:-current} (build host: ${BUILDPLATFORM:-current})"

# Copy package files first for better caching
COPY package.json package-lock.json* ./

# Install dependencies with cache mount
RUN --mount=type=cache,target=/root/.npm \
    npm ci --silent

# Copy source files and build
COPY resources/ ./resources/
COPY vite.config.js tailwind.config.js postcss.config.js ./

# Build production assets
RUN npm run build

# -----------------------------------------------------------------------------
# Stage 2: PHP Dependencies (Composer)
# -----------------------------------------------------------------------------
FROM composer:2.6 AS vendor
LABEL stage="composer-deps"
LABEL description="Install PHP dependencies"

WORKDIR /app

COPY composer.json composer.lock ./

RUN --mount=type=cache,target=/tmp/cache \
    composer install \
        --no-dev \
        --no-scripts \
        --optimize-autoloader \
        --prefer-dist \
        --ignore-platform-req=ext-gd \
        --ignore-platform-req=ext-redis \
        --ignore-platform-req=php

# -----------------------------------------------------------------------------
# Stage 3: Final Runtime Image
# -----------------------------------------------------------------------------
FROM php:8.4-apache AS runtime
LABEL maintainer="HelpSub Team"
LABEL description="HelpSub - Sistema de Controle de Estoque e Emprestimos"
LABEL version="1.0"

ARG TARGETPLATFORM
RUN echo "Runtime image for ${TARGETPLATFORM:-current}"

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    unzip \
    zip \
    postgresql-client \
    netcat-openbsd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Enable Apache modules
RUN a2enmod rewrite headers

# Set working directory
WORKDIR /var/www/html

# Copy vendor dependencies
COPY --from=vendor /app/vendor /var/www/html/vendor

# Copy built frontend assets
COPY --from=frontend /app/public/build /var/www/html/public/build

# Copy application source
COPY . /var/www/html

# Generate optimized autoloader
RUN composer dump-autoload --optimize

# Configure Apache
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/apache/ports.conf /etc/apache2/ports.conf

# Custom PHP configuration
COPY docker/php/helpsub.ini /usr/local/etc/php/conf.d/99-helpsub.ini

# Create log directory for PHP
RUN mkdir -p /var/log/php && chown www-data:www-data /var/log/php

# Create Laravel storage directories
RUN mkdir -p /var/www/html/storage/app/public \
    && mkdir -p /var/www/html/storage/framework/cache \
    && mkdir -p /var/www/html/storage/framework/sessions \
    && mkdir -p /var/www/html/storage/framework/views \
    && mkdir -p /var/www/html/storage/logs \
    && mkdir -p /var/www/html/bootstrap/cache

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Copy and prepare entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
COPY docker/bootstrap-legacy-db.sh /usr/local/bin/bootstrap-legacy-db.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/bootstrap-legacy-db.sh

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=30s --retries=3 \
    CMD curl -f http://localhost:8081/ || exit 1

EXPOSE 8081

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
