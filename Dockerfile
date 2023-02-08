FROM php:8.0-cli

WORKDIR /app

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN apt-get update && apt-get install -y git zip unzip
RUN git config --global --add safe.directory '*'
