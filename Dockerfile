# PHP Webhooks – run with document root = public/
FROM php:8.2-apache

# Build-time version and repo (pass from docker build --build-arg or CI)
ARG GIT_COMMIT=unknown
ARG GIT_TAG=
ARG GIT_REPO_URL=

# Document root so routes like /login work; .htaccess handles rewrites
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf \
    && sed -ri -e "s!/var/www/!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && a2enmod rewrite headers

WORKDIR /var/www/html

COPY config config/
COPY public public/
COPY src src/
COPY templates templates/
COPY .env.example .env.example
COPY docker-entrypoint.sh /var/www/html/docker-entrypoint.sh

# Bake version into image (commit and optional tag)
RUN echo "${GIT_TAG}${GIT_TAG:+ }${GIT_COMMIT}" > /var/www/html/version.txt \
    && chmod +x /var/www/html/docker-entrypoint.sh

# SQLite: writable data dir (runtime volume can override)
RUN mkdir -p data && chown -R www-data:www-data data

# Default env; override with docker run -e or compose env_file.
# GIT_REPO_URL is set from build-arg so the footer shows repo name and link when built from CI/local git.
ENV APP_ENV=production APP_DEBUG=0
ENV GIT_REPO_URL=${GIT_REPO_URL}

ENTRYPOINT ["/var/www/html/docker-entrypoint.sh"]
EXPOSE 80
CMD ["apache2-foreground"]
