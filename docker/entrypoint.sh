#!/bin/sh

# 1. Apagar o .env quebrado que criamos na tentativa anterior
rm -f /var/www/html/.env

# 2. Liberar as variáveis de ambiente limpas para o PHP-FPM
echo "clear_env = no" >> /usr/local/etc/php-fpm.d/www.conf
sed -i 's/;clear_env = no/clear_env = no/g' /usr/local/etc/php-fpm.d/www.conf

# 3. Criar as pastas estruturais que o Laravel precisa
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs

# 4. Compilar todos os arquivos otimizados em Cache (Performance Máxima)
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 5. Rodar as migrações (O Laravel vai ler as variáveis do Railway diretamente agora)
php artisan migrate --force || echo "Migrações falharam. Verifique o banco de dados."

# 6. Ajustar as permissões
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 7. Iniciar o servidor web
exec /usr/bin/supervisord -c /etc/supervisord.conf