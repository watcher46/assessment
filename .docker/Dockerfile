#generate certificates
FROM frapsoft/openssl:latest as openssl
RUN mkdir -p /export; \
    openssl req \
        -x509 \
        -nodes \
        -days 365 \
        -newkey rsa:2048 \
        -subj "/C=NL/ST=Noord-Holland/L=Amsterdam/O=XS4ALL/CN=comments.tweakers.test" \
        -keyout /export/server.key -out /export/server.crt

#install php & extensions
FROM php:7.2-apache
LABEL maintainer="test@test.com"

RUN apt-get update -y && \
    apt-get install -y unzip libzip-dev libxslt-dev libpq-dev git && \
    docker-php-ext-configure zip --with-libzip && \
    docker-php-ext-install pdo xsl pdo_pgsql zip opcache && \
    docker-php-ext-enable opcache


# Copy SSL certificates from intermediate OpenSSL container
COPY --from=openssl /export/ /var/www/certs/

#copy vhost to container
COPY ./.docker/apache/vhosts.conf /etc/apache2/sites-enabled/000-default.conf
RUN a2enmod ssl rewrite

WORKDIR /var/www/html
