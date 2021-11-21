db:
	echo "drop database if exists dbname" | mysql -u root -h database -p${MYSQL_ROOT_PASSWORD}
	echo "create database dbname" | mysql -u root -h database -p${MYSQL_ROOT_PASSWORD}

	bin/console --no-interaction doctrine:migrations:migrate
	bin/console --no-interaction doctrine:fixtures:load
	bin/console doctrine:migrations:sync-metadata-storage

test-db:
	echo "drop database if exists dbname_test" | mysql -u root -h database -p${MYSQL_ROOT_PASSWORD}
	echo "create database dbname_test" | mysql -u root -h database -p${MYSQL_ROOT_PASSWORD}
	bin/console --no-interaction doctrine:migrations:migrate --env=test
	bin/console --no-interaction doctrine:fixtures:load --env=test
	bin/console doctrine:migrations:sync-metadata-storage --env=test

unit-tests:
	./bin/phpunit tests/Unit/*

acceptance-tests-resets-test-database: test-db
	./bin/phpunit tests/Acceptance/*