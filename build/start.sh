#!/bin/sh

if [ ! -d /var/www/html/config ]; then
	cd /var/www/html
	git clone --recursive https://github.com/deejay104/easyaero.git .

	chown www-data documents/
	chown www-data static/cache/
	
else
	cd /var/www/html
	git pull
	git submodule update --remote --recursive
fi

if [ ! -f /var/www/html/config/config.inc.php ]; then

	echo "Creating config file"
	echo "<?php" >/var/www/html/config/config.inc.php
	echo "\$hostname = \"mariadb\";" >>/var/www/html/config/config.inc.php
	echo "\$mysqluser = \"$MYSQL_DATABASE\";" >>/var/www/html/config/config.inc.php
	echo "\$mysqlpassword = \"$MYSQL_PASSWORD\";" >>/var/www/html/config/config.inc.php
	echo "\$db = \"$MYSQL_DATABASE\";" >>/var/www/html/config/config.inc.php
	echo "\$port=3306;" >>/var/www/html/config/config.inc.php
	echo "\$gl_tbl=\"$MYSQL_TABLE\";" >>/var/www/html/config/config.inc.php
	echo "?>" >>/var/www/html/config/config.inc.php
fi

export MYSQL_PASSWORD=xxx

service cron start
service php7.4-fpm start
apachectl -D FOREGROUND
