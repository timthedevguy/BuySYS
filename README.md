AmSYS
=====
AmSYS developed for The Amarr Republic/Allied Industries is an application designed to assist in Eve Online Corp buyback activities.

#### Deployment Guide
I personally have deployed this to GreenGeeks Hosting, DigitalOcean ($5) and self hosted Ubuntu 14 LTS.

1. Install LAMP stack (Apache2, MySQL, PHP 5.x) [DigitalOcean Ubuntu 14 LAMP](https://www.digitalocean.com/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-14-04)
2. Install PHPMyAdmin, will assist in maintenance [DigitalOcean Secure PHPMyAdmin Install](https://www.digitalocean.com/community/tutorials/how-to-install-and-secure-phpmyadmin-on-ubuntu-14-04)
3. Install PHP Bz2 `apt-get install php5.6-bz2` then `phpendmod bz2`, restart server or Apache2
4. Clone Git Repository to `/var/www/html` this will create a folder called 'amsys'
5. Edit `/etc/apache2/sites-enabled/000-default.conf` and add new VirtualHost using your own information.  The important parts are the DocumentRoot and Directory configuration.
```
<VirtualHost *:80>
        ServerName sub.yourdomain.com
        ServerAlias sub.yourdomain.com

        DocumentRoot /var/www/html/amsys/web
        <Directory /var/www/html/amsys/web>
                AllowOverride All
                Order Allow,Deny
                Allow from All
        </Directory>
</VirtualHost>
```
6. Navigate to `/var/www/html/amsys` and download/install [Composer](https://getcomposer.org) with the following commands.
```
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === 'aa96f26c2b67226a324c27919f1eb05f21c248b987e6195cad9690d5c1ff713d53020a02ac8c217dbf90a7eacc9d141d') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
```
6. Using PHPMyAdmin create two databases, one is for Amsys data, the other is for Eve SDE data.  Create a user and GRANT full access on both databases
7. Install dependencies by running `php composer.phar install`, if you a 'Killed' message see Troubleshooting below
 * Accept all defaults when prompted for database_name, database_user, etc.  We edit these later
 * You will get a red error box, this is normal
8. Fix permissions on the application, from the `/var/www/html/amsys` directory, run the following
```
HTTPDUSER=`ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX app/cache app/logs
sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX app/cache app/logs
```
9. Navigate to `/var/www/html/amsys/app/config` and edit parameters.yml, fill in your data for the databases and users
```
# This file is auto-generated during the composer install
parameters:
        database_host: 10.0.1.14
        database_port: 3306
        database_name: amsys_db
        database_user: amsys_user
        database_password: password
        database_host2: 10.0.1.14
        database_port2: 3306
        database_name2: evedata
        database_user2: amsys_user
        database_password2: password
        mailer_transport: smtp
        mailer_host: server.websitehostserver.net
        mailer_port: 465
        mailer_user: no-reply@binarymethod.com
        mailer_password: password
        mailer_encryption: ssl
        mailer_auth_mode: login
        secret: 27cf9b3f2d86b1b6bc6aeaacad9941b5cc1f8a20
```
10. Navigate to `/var/www/html/amsys`, run `php app/console doctrine:schema:update --force` to create database tables
11. Run `php app/console amsys:settings:populate` to create default settings
12. Run `php app/console amsys:sde:update` to download latest SDE from Fuzzworks
13. Run `php app/console amsys:cache:update` to pull all Ore/Minerals/Ice/Gas/PI prices and create the cache
14. Navigate to the web address and you should see login page

### Creating User Account
1. Navigate to http://your.com/register and register an account, API is used ONLY to confirm that you own that character
2. Login to PHPMyAdmin and load the Users table, change `role` from ROLE_USER to ROLE_ADMIN and save.
3. Logout of Amsys and log back in, you know have admin permissions


### Auto Update Cache
Add Cron Job to pull Ore/Minerals/Ice/Gas/PI prices every 15 minutes.

1. crontab -e
2. */15 * * * * php /var/www/html/amsys/app/console amsys:cache:update