#!/bin/sh -le

red=31
green=32
yellow=33
blue=34
i=1
IMAGE_NAME=$1

function cecho {
    color=$1
    shift
    echo "\033[${color}m$@\033[m"
}

function build {
    cecho $green "
###################################################################
# Docker Test Builder
###################################################################
"
    # build branch count
    count=$(ls -l .git/refs/remotes/origin | awk '{print $9}' | awk '{if(length > 0) { if($0 != "HEAD") { print $0}}}' | wc -l | tr -d " ")
    # build start for git branch name
    ls -l .git/refs/remotes/origin | awk '{print $9}' | awk '{if(length > 0) { if($0 != "HEAD") { print $0}}}' | while read BRANCH_NAME; do
        cecho $yellow "
###################################################################
# Build Start ${i}/${count} ${IMAGE_NAME}:${BRANCH_NAME}
###################################################################
"
        git checkout ${BRANCH_NAME} > /dev/null && time docker build --quiet=true --force-rm=true --pull=false --rm=true -t $IMAGE_NAME:$BRANCH_NAME ./

        docker rm -f $(docker ps -q -a) >/dev/null 2>&1
        docker rmi $(docker images -q | head -1)
        docker rmi -f $(docker images | awk '/^<none>/ { print $3 }') >/dev/null 2>&1
        i=$((i+1))
    done

    cecho $green "
###################################################################
# Docker Test Builder End
###################################################################
"
}

if [ -z ${IMAGE_NAME} ]; then
    IMAGE_NAME=$(basename `pwd`)
fi
build
