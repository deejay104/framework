#!/bin/bash

if [ ! -z "$EA_ENV" ]
then
	echo "Environment: $EA_ENV"
	echo "*/5 * * * * www-data php -f /var/www/$EA_ENV/core/scripts/cron.php > /tmp/cron.log">>/etc/crontab
	echo "Starting crontab service"
	service cron start 
fi

echo "Starting PHP FPM"
exec /usr/sbin/php-fpm8.3 --fpm-config /etc/php/8.3/fpm/php-fpm.conf
