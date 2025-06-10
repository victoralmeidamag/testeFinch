FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    gnupg2 \
    unzip \
    curl \
    apt-transport-https \
    lsb-release \
    ca-certificates \
    unixodbc \
    gcc \
    g++ \
    make \
    autoconf \
    libc-dev \
    pkg-config \
    libssl-dev \
    libxml2-dev

RUN curl -sSL https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor -o /etc/apt/keyrings/microsoft.gpg && \
    chmod 644 /etc/apt/keyrings/microsoft.gpg && \
    echo "deb [signed-by=/etc/apt/keyrings/microsoft.gpg] https://packages.microsoft.com/debian/12/prod bookworm main" > /etc/apt/sources.list.d/mssql-release.list && \
    apt-get update && ACCEPT_EULA=Y apt-get install -y msodbcsql18 mssql-tools18 unixodbc-dev

RUN pecl install pdo_sqlsrv sqlsrv && \
    docker-php-ext-enable pdo_sqlsrv sqlsrv

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

CMD ["sh", "-c", "cd /var/www/html && composer dump-autoload && php -S 0.0.0.0:8000 -t /var/www/html"]
