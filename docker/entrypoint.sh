#!/bin/sh

# Substituir a variável $PORT no arquivo de configuração do Nginx
sed -i "s/\${PORT}/${PORT:-80}/g" /etc/nginx/http.d/default.conf

# Rodar migrações se estivermos em produção e for a primeira vez ou necessário
# O Railway recomenda rodar isso no deploy
php artisan migrate --force

# Limpar e gerar cache de configuração para performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Iniciar o Supervisord
exec /usr/bin/supervisord -c /etc/supervisord.conf
