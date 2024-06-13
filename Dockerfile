FROM php:8.3-fpm-alpine AS evenly-distribute

# Non-root user
ARG UID=1000
ARG GID=1000
ARG SCRIPT_URL='https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions'

RUN curl -sSLf -o /usr/local/bin/install-php-extensions ${SCRIPT_URL} && \
    chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions @composer xml && \
    addgroup -g $GID app && adduser -D -u $UID -G app app && addgroup app www-data

USER app

# Source code
COPY --chown=app:app . /var/www/evenly-distribute
WORKDIR /var/www/evenly-distribute

