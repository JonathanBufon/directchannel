FROM php:8.4-cli
RUN apt-get update && apt-get install -y --no-install-recommends \
  git unzip libpq-dev libzip-dev \
  && docker-php-ext-install pdo_pgsql zip \
  && rm -rf /var/lib/apt/lists/*
# Uploads de anexos: o limite da aplicação (A06, padrão 25 MB) fica abaixo destes tetos
RUN printf 'upload_max_filesize=30M\npost_max_size=32M\n' > /usr/local/etc/php/conf.d/uploads.ini
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
