@echo off
docker rm -f web
docker run --network cogg -v %~dp0\app\Resources:/var/www/app/Resources -v %~dp0\web\css:/var/www/web/css -v %~dp0\web\js:/var/www/web/js -v %~dp0\src:/var/www/src -d --name web -p 80:80 --link redis --link database -t binarygod/amsys:docker