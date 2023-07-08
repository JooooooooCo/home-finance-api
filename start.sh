#!/bin/sh
yes yes | php artisan key:generate
yes yes | php artisan migrate
yes yes | php artisan passport:install --force
yes yes | php artisan serve --host=0.0.0.0:8080

#!/usr/bin/env bash
yes yes | php artisan config:cache --no-ansi -q
yes yes | php artisan route:cache --no-ansi -q
yes yes | php artisan view:cache --no-ansi -q
