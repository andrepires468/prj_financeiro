#!/usr/bin/env bash
set -e

# Se a variável PORT estiver definida, assumimos execução no Render.com
# e subimos nginx (escutando em $PORT) + php-fpm no mesmo container.
if [[ -n "${PORT}" ]]; then
  # Renderiza template do nginx substituindo $PORT
  envsubst '${PORT}' < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf

  # Garante permissões de cache/logs do nginx
  mkdir -p /var/tmp/nginx /var/log/nginx
  chown -R www-data:www-data /var/tmp/nginx /var/log/nginx || true

  # Inicia php-fpm no background
  php-fpm -D

  # Inicia nginx em foreground (PID 1)
  exec nginx -g 'daemon off;'
else
  # Ambiente local: apenas php-fpm (nginx roda em outro container)
  exec "$@"
fi


