# Fun with users

This project is based on https://github.com/patrickmaynard/symfony-five-sonata-docker-template and contains PHP 8.0 and a Nginx container.
It comes with these tools/libraries:
* Xdebug (see Dockerfile for options)
* Composer 2
* PHPUnit 9.5
* Symfony (Flex) 4.x
* Sonata Admin, including Sonata User Bundle

Nginx will run on port 8080 (see docker-compose.yml)

These instructions assume you are running Linux or macOS and have Docker installed.

To set up git hooks for running psalm and tests on commit:
```shell
chmod +x .githooks/*
git config --local core.hooksPath .githooks/
```

To start the project, execute tests:
```shell
$ docker-compose up -d
$ cp code/.env.dist code/.env
$ docker-compose exec php composer install
$ docker-compose exec php make db
$ docker-compose exec php make unit-tests
$ docker-compose exec php make acceptance-tests-resets-test-database
```

To regenerate keys for the Lexik JWT authentication bundle, do the following:
```
$ docker-compose exec php bin/console lexik:jwt:generate-keypair
```

To log on via the API as a superuser, send a POST request to http://localhost:8080/api/login_check with a `Content-Type` header of `application/json` and the following raw body:

```json
{"username":"superuser","password":"password"}
``` 

**Or even better, just use the endpoint in the included Postman collection.**

(That Postman collection showing examples can be found in directory `background-and-examples`, along with UML diagrams and a database layout.)

This will give you a token. You can use this token for API authentication as described on this page: 

https://github.com/lexik/LexikJWTAuthenticationBundle/blob/2.x/Resources/doc/index.md#usage

## Using the User API endpoints

### Create

To create a user, send an authenticated POST request to the `/api/user` endpoint that is saved in the Postman collection.

Along with your bearer token, you will need a body -- something like this:

```json
{
    "userName":"bobuser19",
    "email":"bobuser19@example.com",
    "plainPassword":"password19",
    "enabled":true
}
```

This endpoint will return a JSON-serialized representation of the new user, with some fields intentionally omitted for brevity.

### Delete

To delete a user, send an authenticated DELETE request to `/api/user/someusername`, substituting in the name of the user you wish to delete.

## Using the UserGroup API endpoints

### Create

To create a user group, send an authenticated POST request `/api/user-group` to with a body like the following example:

```json
{
  "name":"group5",
  "roles":["ROLE_USER","ROLE_FOO"]
}
```

### Delete

To delete a user group, send an authenticated DELETE request to `localhost:8080/api/user-group/somegroupname`, substituting in the name of the group you wish to delete.

### Add user to group

To add a user to a group that they are not a part of, send an authenticated LINK request to `localhost:8080/api/user-group/somegroupname/someusername`, substituting in group and user names.

### Remove user from group

To remove a user from a group that they are not a part of, send an authenticated UNLINK request to `localhost:8080/api/user-group/somegroupname/someusername`, substituting in group and user names.

### Additional actions

If you want to create, list and modify users and groups in a browser, go to http://localhost:8080/admin/app/sonatauseruser/list and login with username `superuser` and password `password`. Those credentials should obviously be changed as soon as possible.

The user interface will be fairly self-explanatory from that point.

UML diagrams, a database entity relationship diagram and a Postman collection showing examples can be found in directory `background-and-examples`.

Documentation I used for setting up the Sonata user bundle:

https://docs.sonata-project.org/projects/SonataUserBundle/en/4.x/reference/installation/

High-priority TODO items:
    
x Refactor tests to remove duplication of code.
x Add an endpoint to the API group controller that allows an admin to list all users in a group.
x Update fixtures and test logic so you're using a more neutral group name than "customers".
* Fix/add integration tests for RUD operations on API user endpoints. 
* Fix/add integration tests for CUD operations on API group endpoints.
* Update test logic to cover edge cases.
* Also add a couple basic unit tests of Sonata classes to make sure they're installed.
* Refactor to abstract some more controller logic into services. The controllers are a bit fat now, and there's some duplicated code.
* Create a custom UserManager class to replace the deprecated FOSUserBundle version, and move some logic from the UserService to that manager.
* Make endpoint urls adhere to the "APIs You Don't Hate" standard. (This will be fairly simple.)
* Add versioning to the user API -- either via a route prefix or via a header.
* Add pagination to the endpoints that list users.
* Add something like /api/users/me for CRUDing a user's own information. (Currently, only admins can retrieve user info.)
* Audit tests for isolation. (Some early tests may not have been well isolated.)

Low-priority TODO items (convert to GitHub issues before doing further work):

* Create OpenAPI documentation (not a priority because of the Postman collection).
* Make sure that your documentation page's sandboxing feature works.
* Make psalm stricter by one level, then move this item to a lower priority task and repeat after several other tasks.
* Grow the length of time each JWT is valid for -- right now, it expires after an hour.
* In user creation endpoint, have serializer ignore any new properties by default instead of needing to update an entity class constant.
* This should be done by converting from the Symfony serializer to the JMS serializer and using serialization groups.
* Move hard-coded strings into class constants.
* Handle any unhandled Guzzle exceptions in tests
