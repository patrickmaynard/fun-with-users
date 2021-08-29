# Symfony project template

This project contains PHP 8.0 and a Nginx container.
It comes with these tools/libraries:
* Xdebug (see Dockerfile for options)
* Composer 2
* PHPUnit 9.5
* Symfony (Flex) 4.x

Nginx will run on port 8080 (see docker-compose.yml)

To set up git hooks:
```shell
chmod +x .githooks/*
git config --local core.hooksPath .githooks/
```

To start the project & execute tests:
```shell
$ docker-compose up -d
$ docker-compose exec php composer install
$ docker-compose exec php make db
$ docker-compose exec php make unit-tests
$ docker-compose exec php make acceptance-tests-resets-test-database
```

To make a new entity:
```
$ docker-compose exec php console make:entity MyEntityName --force-annotation
```
