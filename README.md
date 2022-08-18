# Home Finance

## About

Home finance is an app created for manage family accounts, but it can be used for small business too.

## API

This repo is the backend for Home Finance app.
It was build with Laravel PHP, to serve Rest APIs.

## Installation
After download the project, rename the file `.env.example` to `.env` and add your database credentials in the variables:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```

Now, you will need `composer` installed, to install all dependencies.
Run all commands below:

```
composer install
```
```
php artisan key:generate
```
```
php artisan migrate
```
```
php artisan db:seed
```
```
php artisan serve --host=YOURHOST --port=YOURPORT
```

Great, now the api is available at `YOURHOST:YOURPORT`

I hope you enjoy it :]