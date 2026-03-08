FROM php:8.2-fpm

# Instalação de dependências e extensões PHP necessárias
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx \
    gettext-base \
  && rm -rf /var/lib/apt/lists/*

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
    /var/www/storage \
    /var/www/bootstrap/cache

# Configuração Nginx (para Render.com)
# Usaremos um template e substituiremos $PORT em tempo de execução
COPY docker/nginx.conf.template /etc/nginx/conf.d/default.conf.template
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expõe portas padrão (php-fpm e http)
EXPOSE 9000 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["php-fpm"]
