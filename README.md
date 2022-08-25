# Home Finance ![Logo](./storage/api-docs/api-logo.png?raw=true "Logo")

## About

Home finance is an app created for manage family accounts, but it can be used for small business too.

It was built with:
<div>
<img src="./storage/api-docs/php-logo.png" alt="php" height="30"/>
&nbsp;
<img src="./storage/api-docs/laravel-logo.png" alt="laravel" height="30"/>
&nbsp;
<img src="./storage/api-docs/swagger-logo.png" alt="swagger" height="30"/>
&nbsp;
<img src="./storage/api-docs/openapi-logo.png" alt="openapi" height="30"/>
</div>


## API

This repo is the backend for Home Finance app and it serves Rest APIs.

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

Great, now the API is available at `YOURHOST:YOURPORT`

## Documentation

You can check documentation and test all the endpoints on swagger, available at `YOURHOST:YOURPORT/api/documentation`

To test endpoints using authorization, use the Bearer token provided by the `api/user-register` or `api/user-login` endpoint.

Find below a screenshot of API documentation

![API Documentation](./storage/api-docs/api-docs.png?raw=true "API Documentation")

## I hope you enjoy it! 8)