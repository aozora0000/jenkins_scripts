docker run -d --name db mysql:5.6.22 && \
export LINK_1="--link db:db" && \
docker run -d --name redis redis:2.8.9 && \
export LINK_2="--link redis:redis"