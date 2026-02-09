FROM php:8.2-fpm-alpine

# Установка системных зависимостей
RUN apk add --no-cache --update \
    build-base \
    autoconf \
    postgresql-dev \
    libpq \
    libzip-dev \
    zlib-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libxml2-dev \
    icu-dev \
    oniguruma-dev \
    git \
    curl \
    zip \
    unzip && \
    # Установка расширений PHP
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) \
        pdo_pgsql \
        pgsql \
        mbstring \
        zip \
        gd \
        intl \
        opcache && \
    # Установка Redis
    pecl install redis-6.0.2 && \
    docker-php-ext-enable redis && \
    # Очистка кэша пакетов
    apk del --no-cache build-base autoconf postgresql-dev libzip-dev zlib-dev \
        libpng-dev libjpeg-turbo-dev freetype-dev libxml2-dev icu-dev oniguruma-dev

# Установка Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Настройка владельца (www-data = uid 82 в Alpine)
RUN mkdir -p /app/runtime /app/web/assets && \
    chown -R www-data:www-data /app/runtime /app/web/assets && \
    chmod -R 755 /app/runtime /app/web/assets

# Рабочая директория
WORKDIR /app

# Запуск от пользователя www-data (безопаснее, чем от root)
USER www-data

CMD ["php-fpm"]