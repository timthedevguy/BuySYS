#!/bin/sh

docker network create cogg

docker run --network cogg -d --name database -p 3306 -t tehraven/alpinewebos:mariadb
docker run --network cogg -d --name redis -p 6379 -t tehraven/alpinewebos:redis
docker run --network cogg -d --name phpmyadmin -p 8080:80 --link database --env PMA_HOST=database -t phpmyadmin/phpmyadmin:latest

docker build --network cogg -t binarygod/amsys:docker .
docker run --network cogg -v %~dp0\src:/var/www/src -d --name web -p 80:80 --link redis --link database -t binarygod/amsys:docker

docker exec web php app/console doctrine:schema:update --force
docker exec web php app/console amsys:settings:populate
docker exec web php app/console amsys:sde:update
docker exec web php app/console amsys:cache:update

docker ps
docker logs web