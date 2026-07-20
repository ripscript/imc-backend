composer install

chown -R www-data:www-data /var/www/html/storage/*

php artisan key:generate
php artisan optimize:clear
php artisan storage:link

php artisan serve --host 0.0.0.0