FROM composer:latest
COPY . /src
ADD .env.example /src/.env
WORKDIR /src
RUN composer install
RUN php artisan key:generate
RUN chmod -R 777 storage/
CMD php artisan serve --host=0.0.0.0