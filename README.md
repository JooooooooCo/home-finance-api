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

Now, you will need `docker` installed. Run this command to build and up the docker container:

```
$ docker-compose up -d
```

Then, run the below commands to prepare the app:

```
$ docker exec home_finance_api php artisan key:generate
```
```
$ docker exec home_finance_api php artisan migrate
```
```
$ docker exec home_finance_api php artisan passport:install --force
```
Finally, we'll need to run the following command to serve the app. This must be done every time the container is initialized.

```
$ docker exec home_finance_api php artisan serve --host=0.0.0.0 --port=8000
```

Great, now the API is available at `127.0.0.1:8000`

## Documentation

You can check documentation and test all the endpoints on swagger, available at `127.0.0.1:8000/api/documentation`

To test endpoints using authorization, use the Bearer token provided by the `api/user/register` or `api/user/login` endpoint.

Find below a screenshot of API documentation

![API Documentation](./storage/api-docs/api-docs.png?raw=true "API Documentation")

### Update swagger documentation

If you need update swagger documentation, you'll need run below commands.

```
$ docker exec home_finance_api php artisan l5-swagger:generate
```

```
$ docker exec home_finance_api php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
```

## I hope you enjoy it! 8)