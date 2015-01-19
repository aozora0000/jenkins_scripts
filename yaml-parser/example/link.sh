docker run -d --name db mysql:5.6.22 && \
export LINK_1="--link db:db"