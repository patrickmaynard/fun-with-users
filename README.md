# Fun with users

This project is based on https://github.com/patrickmaynard/symfony-five-sonata-docker-template and contains PHP 8.0 and a Nginx container.
It comes with these tools/libraries:
* Xdebug (see Dockerfile for options)
* Composer 2
* PHPUnit 9.5
* Symfony (Flex) 4.x
* Sonata Admin, including Sonata User Bundle

Nginx will run on port 8080 (see docker-compose.yml)

To set up git hooks for running psalm and tests on commit:
```shell
chmod +x .githooks/*
git config --local core.hooksPath .githooks/
```

To start the project & execute tests (of which there aren't many, since this project doesn't do anything custom yet):
```shell
$ docker-compose up -d
$ docker-compose exec php composer install
$ docker-compose exec php make db
$ docker-compose exec php make unit-tests
$ docker-compose exec php make acceptance-tests-resets-test-database
```

To start creating and modifying users, go to http://localhost:8080/admin/app/sonatauseruser/list and login with username `superuser` and password `password`. Those credentials should obviously be changed.

The user interface will be fairly self-explanatory from that point.

Docs used for setting up the Sonata user bundle:
```
https://docs.sonata-project.org/projects/SonataUserBundle/en/4.x/reference/installation/
```

