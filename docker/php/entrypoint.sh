#!/bin/sh

echo "$(date '+%F %T') | Aguardando SQL Server ficar disponível..."

until php /var/www/html/migrate.php; do
  echo "$(date '+%F %T') | SQL Server ainda não está pronto. Tentando novamente em 3 segundos..."
  sleep 3
done

echo "$(date '+%F %T') | Migração executada."

echo "$(date '+%F %T') | Configurando ambiente PHP e dependências..."

if [ ! -f "/var/www/html/composer.json" ]; then
    echo "$(date '+%F %T') | composer.json não encontrado, criando um básico..."
    cat > /var/www/html/composer.json << 'EOF'
{
    "require": {
        "php": ">=8.3",
        "zircote/swagger-php": "^4.0",
        "symfony/dependency-injection": "^5.0|^6.0|^7.0",
        "symfony/finder": "^5.0|^6.0|^7.0"
    },
    "autoload": {
        "psr-4": {
            "App\\": "./"
        }
    },
    "config": {
        "optimize-autoloader": true,
        "allow-plugins": {
            "*": true
        }
    }
}
EOF
fi

echo "$(date '+%F %T') | Limpando cache do Composer..."
composer clear-cache

echo "$(date '+%F %T') | Instalando dependências do Composer..."
composer install --no-interaction --prefer-dist --optimize-autoloader || {
    echo "$(date '+%F %T') | Falha no composer install, tentando composer update..."
    composer update --no-interaction --prefer-dist
}


echo "$(date '+%F %T') | Otimizando autoload..."
composer dump-autoload --optimize


echo "$(date '+%F %T') | Iniciando servidor PHP embutido..."
php -S 0.0.0.0:8000 -t /var/www/html
