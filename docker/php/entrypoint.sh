#!/bin/sh

echo "$(date '+%F %T') | Aguardando SQL Server ficar disponível..."

until php /var/www/html/migrate.php; do
  echo "$(date '+%F %T') | SQL Server ainda não está pronto. Tentando novamente em 3 segundos..."
  sleep 3
done

echo "$(date '+%F %T') | Migração executada."

echo "$(date '+%F %T') | Configurando ambiente PHP e dependências..."

# Verificar se o composer.json existe, senão criar um básico
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
            "App\\": "src/"
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

# Limpar cache e resolver conflitos
echo "$(date '+%F %T') | Limpando cache do Composer..."
composer clear-cache

# Instalar/atualizar dependências com resolução de conflitos
echo "$(date '+%F %T') | Instalando dependências do Composer..."
composer install --no-interaction --prefer-dist --optimize-autoloader || {
    echo "$(date '+%F %T') | Falha no composer install, tentando composer update..."
    composer update --no-interaction --prefer-dist
}

# Garantir que swagger-php está instalado com dependências corretas
echo "$(date '+%F %T') | Verificando swagger-php..."
if ! composer show zircote/swagger-php > /dev/null 2>&1; then
    echo "$(date '+%F %T') | Instalando zircote/swagger-php com dependências..."
    composer require --no-interaction zircote/swagger-php:^4.0 symfony/dependency-injection:^5.0|^6.0|^7.0
fi

# Gerar autoload otimizado
echo "$(date '+%F %T') | Otimizando autoload..."
composer dump-autoload --optimize

echo "$(date '+%F %T') | Verificando se OpenApi\\Generator está disponível..."
php -r "
require_once '/var/www/html/vendor/autoload.php';
if (class_exists('OpenApi\\Generator')) {
    echo 'SUCCESS: OpenApi\\Generator está disponível!' . PHP_EOL;
} else {
    echo 'ERROR: OpenApi\\Generator não está disponível!' . PHP_EOL;
    echo 'Pacotes relacionados ao swagger:' . PHP_EOL;
    system('composer show | grep -i swagger || echo \"Nenhum pacote swagger encontrado\"');
    exit(1);
}
"

if [ $? -eq 0 ]; then
    echo "$(date '+%F %T') | Gerando Swagger JSON..."
    if [ -f "/var/www/html/generate-swagger.php" ]; then
        php /var/www/html/generate-swagger.php || echo "$(date '+%F %T') | Erro ao gerar Swagger JSON"
    else
        echo "$(date '+%F %T') | Arquivo generate-swagger.php não encontrado!"
    fi
else
    echo "$(date '+%F %T') | Pulando geração do Swagger devido a erro na verificação de dependências"
fi

echo "$(date '+%F %T') | Iniciando servidor PHP embutido..."
php -S 0.0.0.0:8000 -t /var/www/html
