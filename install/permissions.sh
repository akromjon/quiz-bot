#!/bin/bash
find /var/www/biologiya.testhub.uz/ -type d -exec chmod 755 {} \;
find /var/www/biologiya.testhub.uz/ -type f -exec chmod 644 {} \;
chmod -R 775 /var/www/biologiya.testhub.uz/storage/
chmod -R 775 /var/www/biologiya.testhub.uz/bootstrap/cache
sudo chown -R www-data:www-data /var/www/biologiya.testhub.uz
