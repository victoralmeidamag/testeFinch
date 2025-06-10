#!/bin/sh

echo "Aguardando SQL Server ficar disponível..."

until php /var/www/html/migrate.php; do
  echo "SQL Server ainda não está pronto. Tentando novamente em 3 segundos..."
  sleep 3
done

echo "Migração executada. Iniciando servidor PHP embutido..."
cd /var/www/html
composer dump-autoload
php -S 0.0.0.0:8000 -t /var/www/html
