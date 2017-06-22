@echo off

FOR /f "tokens=*" %%i IN ('docker ps -a -q') DO docker rm -f %%i

docker network create cogg
docker run --network cogg -d --name redis -p 6379:6379 -t tehraven/alpinewebos:redis
docker run --network cogg -d --name database -p 3306:3306 -t tehraven/alpinewebos:mariadb
docker run --network cogg -d --name phpmyadmin -p 8080:80 --link database --env PMA_HOST=database -t phpmyadmin/phpmyadmin:latest


echo "Manual pause. Gimme 10 seconds for the DB server to fully online..."
pause

docker build --no-cache --network cogg -t binarygod/amsys:docker .
docker run --network cogg -d --name web -p 80:80 --link redis --link database -t binarygod/amsys:docker
  
docker ps
docker logs web