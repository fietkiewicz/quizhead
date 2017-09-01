#!/bin/sh

echo
echo
echo "       >>>>>>> IMPORTANT <<<<<< "
echo " When you set the root password on the database set it"
echo " to 'tg6y4L*' without the quotes. It is blank initially!!!"
echo "       >>>>>>> IMPORTANT <<<<<< "

mysql_secure_installation
mysql -u root --password=tg6y4L* < data/create-app-db.sql
mysql -u root --password=tg6y4L* sagame < data/sa-game.sql
