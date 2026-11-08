# Imagen de PHP con Apache para desplegar en Render
FROM php:8.3-apache

# Extensiones para conectarse a PostgreSQL (Render) y MySQL (local)
RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pdo_pgsql pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Copia el proyecto a la carpeta pública de Apache
COPY . /var/www/html/

# Render indica el puerto en la variable PORT; Apache escucha en ese puerto
CMD sed -i "s/Listen 80/Listen ${PORT:-80}/" /etc/apache2/ports.conf \
    && sed -i "s/:80>/:${PORT:-80}>/" /etc/apache2/sites-available/000-default.conf \
    && apache2-foreground
