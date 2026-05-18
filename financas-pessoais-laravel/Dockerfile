FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y \
  curl git zip unzip software-properties-common \
  && add-apt-repository ppa:ondrej/php \
  && apt-get update && apt-get install -y \
  libpng-dev libonig-dev libxml2-dev \
  && rm -rf /var/lib/apt/lists/*

# Node 20
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
  && apt-get install -y nodejs

# PHP 8.3 + extensions
RUN apt-get update && apt-get install -y \
  php8.3 php8.3-fpm php8.3-cli php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-intl php8.3-gd php8.3-mysql \
  && rm -rf /var/lib/apt/lists/*

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html
CMD ["bash", "-lc", "php artisan serve --host=0.0.0.0 --port=8000"]
