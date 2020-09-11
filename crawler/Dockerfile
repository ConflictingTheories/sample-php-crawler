FROM php:7.2-apache

# Install Build Dependencies & tools
RUN apt-get update && apt-get install -y \
    cron \
    anacron \
    git \
    nmap \
    htop \
    libpcre3-dev \
    libmcrypt-dev \
    libxml2-dev \
    zlib1g-dev \
    libssh2-1 \
    libssh2-1-dev \
    libpng-dev \
    --no-install-recommends \
    && docker-php-ext-install -j$(nproc) pdo_mysql mbstring soap zip gd \
    && cp /usr/local/bin/php /usr/bin/

# Install PECL extensions
RUN pecl install xdebug && pecl install mcrypt-1.0.1 && pecl install ssh2-1.1.2
RUN docker-php-ext-enable xdebug mcrypt ssh2

# Install Phalcon Extension
WORKDIR /usr/local/src
RUN git clone https://github.com/phalcon/cphalcon.git --branch v3.3.1 --single-branch
WORKDIR /usr/local/src/cphalcon/build
RUN ./install

# Activate PHP Extensions
WORKDIR /etc/php7/mods-available
RUN echo 'extension=phalcon.so' >> phalcon.ini
RUN docker-php-ext-enable phalcon
RUN a2enmod rewrite

# Add Source
COPY ./src /var/www/html

# Working Directory
WORKDIR /var/www/html
VOLUME ["website"]

# Install Composer Locally
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"; \
 php -r "if (hash_file('sha384', 'composer-setup.php') === '8a6138e2a05a8c28539c9f0fb361159823655d7ad2deecb371b04a83966c61223adc522b0189079e3e9e277cd72b8897') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"; \
 php composer-setup.php; \
 php -r "unlink('composer-setup.php');" ; \
 php composer.phar require masterminds/html5; \
 php composer.phar install;

EXPOSE 80