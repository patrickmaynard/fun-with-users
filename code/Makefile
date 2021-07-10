db:
	echo "drop database if exists dbname" | mysql -u root -h database -p${MYSQL_ROOT_PASSWORD}
	echo "create database dbname" | mysql -u root -h database -p${MYSQL_ROOT_PASSWORD}
	bin/console --no-interaction doctrine:migrations:migrate
	bin/console --no-interaction doctrine:fixtures:load

