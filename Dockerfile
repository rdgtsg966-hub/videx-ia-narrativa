FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    ffmpeg \
    git \
    curl \
    unzip \
    && a2enmod rewrite

RUN mkdir -p /var/www/html/uploads/narrativa \
    && chmod -R 777 /var/www/html/uploads

COPY . /var/www/html/

RUN chmod +x /var/www/html/start.sh

EXPOSE 10000

CMD ["/var/www/html/start.sh"]
