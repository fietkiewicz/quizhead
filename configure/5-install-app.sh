#!/bin/sh

# Create our private directory for the app
mkdir /var/php
mkdir /var/php/tmp
tar -xzf data/stmCore.tar.gz -C /var/php

# Set permissions on app directory
chown -R root /var/php
chgrp -R www-data /var/php
chmod -R 755 /var/php/
chmod 775 /var/php/tmp

# Remove the default index that ships with Apache
rm /var/www/html/index.html

# Create symlinks to public facing content in app directory
ln -s /var/php/stmCore/css /var/www/html/css
ln -s /var/php/stmCore/js /var/www/html/js
ln -s /var/php/stmCore/index.php /var/www/html/index.php


