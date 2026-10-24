FROM php:8.2.35-fpm

# install-php-extensions resolve dependências automaticamente para qualquer extensão
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions

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
  && rm -f /etc/nginx/sites-enabled/default \
  && rm -rf /var/lib/apt/lists/*

# Instala extensões PHP (imap via install-php-extensions que resolve libc-client no Bookworm)
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
  && install-php-extensions imap

# Instala e configura Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.idekey=PHPSTORM" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.log_level=0" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini


# Configuração do Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define diretório de trabalho
WORKDIR /var/www

# Copia arquivos do projeto
COPY . /var/www

# Instalação das dependências do Composer
RUN composer install --no-dev --optimize-autoloader

# Garante que os diretórios necessários existam e configura permissões
RUN mkdir -p /var/www/bootstrap/cache \
    && mkdir -p /var/www/storage/logs \
    && mkdir -p /var/www/storage/framework/cache \
    && mkdir -p /var/www/storage/framework/sessions \
    && mkdir -p /var/www/storage/framework/views \
    && chown -R www-data:www-data \
        /var/www/storage \
        /var/www/bootstrap/cache

# Configuração Nginx (para Render.com)
# Usaremos um template e substituiremos $PORT em tempo de execução
COPY docker/nginx.conf.template /etc/nginx/conf.d/default.conf.template
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN sed -i 's/\r//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

# Expõe portas padrão (php-fpm e http)
EXPOSE 9000 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["php-fpm"]
