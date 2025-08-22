FROM php:8.2-fpm

# Instalação de dependências e extensões PHP necessárias
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Instala extensões PHP
#pdo_mysql mysqli curl openssl fileinfo mbstring imap exif pcntl bcmath gd
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd


# Configuração do Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define diretório de trabalho
WORKDIR /var/www

# Copia arquivos do projeto
COPY . /var/www

# Instalação das dependências do Composer
RUN composer install --no-dev --optimize-autoloader

# Configura permissões
RUN chown -R www-data:www-data \
    /var/www/storage

# Remove a linha específica do arquivo SqlServerConnector.php
# RUN sed -i '19d' /var/www/vendor/illuminate/database/Connectors/SqlServerConnector.php

# Expõe porta 9000
EXPOSE 9000

CMD ["php-fpm"]
