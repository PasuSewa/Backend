web: vendor/bin/heroku-php-apache2 public/
worker: php artisan queue:restart && php artisan queue:work database --sleep=3 --tries=1