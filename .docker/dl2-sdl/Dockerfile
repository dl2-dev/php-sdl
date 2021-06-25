# FROM php:7.2-fpm
# FROM php:7.3-fpm
# FROM php:7.4-fpm
FROM php:8.0-fpm

ENV DEBIAN_FRONTEND noninteractive
ARG WITH_MYSQL
ARG WITH_PGSQL
ARG WITH_REDIS
ARG WITH_XDEBUG

RUN apt-get update --fix-missing
#RUN apt-get install -yq --no-install-recommends \
#  apt-utils \
#  build-essential \
#  procps

RUN apt-get install -yq --no-install-recommends \
  git \
  libbz2-dev \
  libicu-dev \
  libmpc-dev \
  libonig-dev \
  libpng-dev \
  libxml2-dev \
  libzip-dev \
  zlib1g-dev

RUN docker-php-ext-install \
  bcmath \
  bz2 \
  calendar \
  exif \
  gd \
  gmp \
  soap \
  sockets \
  zip

RUN if [ ! -z "$WITH_MYSQL" ]; \
  then \
    docker-php-ext-install pdo_mysql \
    ; \
  fi;

RUN if [ ! -z "$WITH_REDIS" ]; \
  then \
    pecl install redis && docker-php-ext-enable redis \
    ; \
  fi;

RUN if [ ! -z "$WITH_XDEBUG" ]; \
  then \
    pecl install xdebug && docker-php-ext-enable xdebug \
    ; \
  fi;

RUN if [ ! -z "$WITH_PGSQL" ]; \
  then \
    apt-get install -y --no-install-recommends libpq5 libpq-dev \
    && docker-php-ext-install pdo_pgsql \
    ; \
  fi;

# Clean up
RUN apt-get -yq purge $(dpkg --get-selections | awk '{print $1}' | grep '\-dev$') \
  && apt-get -yq autoremove --purge \
  && apt-get -yq clean \
  && rm -rf /var/lib/apt/lists/*
