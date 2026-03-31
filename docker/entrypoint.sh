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
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 4. Rodar as migrações
php artisan migrate --force || echo "Migrações falharam. Verifique o banco de dados."

# 5. Ajustar as permissões DEPOIS do artisan
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 6. Iniciar o servidor web
exec /usr/bin/supervisord -c /etc/supervisord.conf