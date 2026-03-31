#!/bin/sh

# 1. Liberar as variáveis de ambiente (Supabase) para o PHP-FPM ler
echo "clear_env = no" >> /usr/local/etc/php-fpm.d/www.conf
sed -i 's/;clear_env = no/clear_env = no/g' /usr/local/etc/php-fpm.d/www.conf

# 2. Criar as pastas estruturais que o Laravel precisa
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs

# 3. Rodar as migrações e o cache (Isso pode criar arquivos novos como root)
php artisan migrate --force || echo "Migrações falharam. Verifique o banco de dados."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Ajustar as permissões DEPOIS do artisan (Dá a posse de tudo para o Nginx/PHP)
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 5. Iniciar o servidor web
exec /usr/bin/supervisord -c /etc/supervisord.conf