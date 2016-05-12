AmSYS
=====
AmSYS developed for The Amarr Republic/Allied Industries is an application designed to assist in Eve Online Corp activities.

#### Current Features

1. Clone Git repository
2. Download Composer (getcomposer.org)
3. Install dependencies (php composer.phar install)
4. Update parameters.yml
5. create database + users
6. run doctrine:schema:update --force
7. run db:populate
8. import invTypes.sql
9. run auto:update

Optional
Add Cron Job
 1. crontab -e
 2. */15 * * * * php /home/binarygod/public_html/binaryforms.com/amsys/app/console auto:update