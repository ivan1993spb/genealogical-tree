#!/bin/bash

VOLUME_HOME="/var/lib/mysql"

sed -ri -e "s/^upload_max_filesize.*/upload_max_filesize = ${PHP_UPLOAD_MAX_FILESIZE}/" \
    -e "s/^post_max_size.*/post_max_size = ${PHP_POST_MAX_SIZE}/" /etc/php5/apache2/php.ini
if [[ ! -d $VOLUME_HOME/mysql ]]; then
    echo "=> An empty or uninitialized MySQL volume is detected in $VOLUME_HOME"
    echo "=> Installing MySQL ..."
    mysql_install_db > /dev/null 2>&1
    echo "=> Done!"
    /create_mysql_admin_user.sh

    if [ -f /app/db.sql ]; then
        echo "<<<INSTALLING DATABASE START"
        service mysql start
        mysql < /app/db.sql
        service mysql stop
        rm /app/db.sql
        echo "INSTALLING DATABASE END>>>"
    fi
else
    echo "=> Using an existing volume of MySQL"
fi

exec supervisord -n
