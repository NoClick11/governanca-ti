#!/bin/sh

# 1. A MÁGICA: Extrair as variáveis do Railway e forçar a criação do arquivo .env
env > /var/www/html/.env

# 2. Liberar as variáveis no PHP-FPM (mantemos por segurança extra)
echo "clear_env = no" >> /usr/local/etc/php-fpm.d/www.conf
sed -i 's/;clear_env = no/clear_env = no/g' /usr/local/etc/php-fpm.d/www.conf

# 3. Criar as pastas estruturais que o Laravel precisa
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs

# 4. Limpar os caches para garantir que ele leia o novo arquivo .env recém-criado
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 5. Rodar as migrações (agora ele FINALMENTE vai enxergar o Supabase)
php artisan migrate --force || echo "Migrações falharam. Verifique o banco de dados."

# 6. Ajustar as permissões de tudo, incluindo o novo .env
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/.env

# 7. Iniciar o servidor web
exec /usr/bin/supervisord -c /etc/supervisord.conf