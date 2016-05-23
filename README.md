AmSYS
=====
AmSYS developed for The Amarr Republic/Allied Industries is an application designed to assist in Eve Online Corp activities.

#### Deployment Guide
I personally have deployed this to GreenGeeks Hosting and self hosted Ubuntu Server 14.x using these general guidelines.

1. Clone Git repository to /public_html/<site> - This will create a folder named 'amsys'
2. Download Composer (getcomposer.org)
3. Install dependencies by cd'ing to '/public_html/<site>/amsys' and running 'php composer.phar install'
4. Create two databases, one for Amsys and one for Eve SDE
5. Update parameters.yml with values for your database and users
6. Run 'php app/console doctrine:schema:update --force' to update the Amsys database
7. Run 'php app/console db:populate' to force system to write database values
8. Import invTypes.sql to Eve specific database via CLI or PHPMyAdmin
9. Run 'php app/console auto:update' to pull all Ore/Minerals/Ice/Gas/PI prices and create the cache

#### Optional Auto Update Cache
Add Cron Job to pull Ore/Minerals/Ice/Gas/PI prices every 15 minutes.

1. crontab -e
2. */15 * * * * php /home/binarygod/public_html/binaryforms.com/amsys/app/console auto:update