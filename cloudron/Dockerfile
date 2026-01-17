FROM cloudron/base:4.2

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
  && apt-get install -y software-properties-common curl ca-certificates gnupg unzip \
  && add-apt-repository ppa:ondrej/php -y \
  && apt-get update \
  && apt-get install -y \
    php8.4-cli \
    php8.4-fpm \
    php8.4-mysql \
    php8.4-redis \
    php8.4-xml \
    php8.4-mbstring \
    php8.4-zip \
    php8.4-curl \
    php8.4-gd \
    php8.4-bcmath \
  && rm -rf /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app/code
COPY . /app/code

RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader \
  && rm -rf /app/code/node_modules

RUN chmod +x /app/code/cloudron/start.sh

EXPOSE 8000

CMD ["/app/code/cloudron/start.sh"]
