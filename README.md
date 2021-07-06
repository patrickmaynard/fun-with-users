# Symfony project template

This project contains a PHP 8.0 and a Nginx container.
It includes the following tools/libraries:
* Xdebug (see Dockerfile for options)
* Composer 2
* PHPUnit 9.5
* Full Symfony (Flex) 5.x

Nginx will run on port 8080 (see docker-compose.yml)

To start the project & execute tests:
```shell
$ docker-compose up -d
$ docker-compose exec php composer install
$ docker-compose exec php ./bin/phpunit
```
