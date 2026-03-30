#!/bin/sh

# Substituir a variável __PORT__ no arquivo de configuração do Nginx
sed -i "s/__PORT__/${PORT:-80}/g" /etc/nginx/http.d/default.conf

# Garantir que os diretórios necessários existem e têm as permissões corretas
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Rodar migrações se estivermos em produção e for a primeira vez ou necessário
# O Railway recomenda rodar isso no deploy
php artisan migrate --force || echo "Migrações falharam. Verifique o banco de dados."

# Limpar e gerar cache de configuração para performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Iniciar o Supervisord
exec /usr/bin/supervisord -c /etc/supervisord.conf
