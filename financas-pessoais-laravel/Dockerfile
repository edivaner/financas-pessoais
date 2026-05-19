FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y \
  curl git zip unzip software-properties-common \
  && add-apt-repository ppa:ondrej/php \
  && apt-get update && apt-get install -y \
  libpng-dev libonig-dev libxml2-dev \
  && rm -rf /var/lib/apt/lists/*

# PHP 8.3 + extensions (PostgreSQL)
RUN apt-get update && apt-get install -y \
  php8.3 php8.3-cli php8.3-mbstring php8.3-xml php8.3-curl \
  php8.3-zip php8.3-intl php8.3-gd php8.3-pgsql php8.3-pdo \
  && rm -rf /var/lib/apt/lists/*

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN printf "upload_max_filesize=15M\npost_max_size=16M\n" > /etc/php/8.3/cli/conf.d/99-uploads.ini

WORKDIR /var/www/html

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

CMD ["docker-entrypoint.sh"]
