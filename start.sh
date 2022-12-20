#!/bin/sh
 php artisan key:generate
 php artisan migrate
 php artisan passport:install --force
 php artisan serve --host=0.0.0.0
