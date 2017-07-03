FROM ubuntu:xenial
MAINTAINER "https://github.com/tehraven"
# BUILDS tehraven/alpinewebos:redis
 
RUN apt-get update && apt-get install -y redis-server

EXPOSE 6379

CMD  ["/usr/bin/redis-server"]