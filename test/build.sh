#!/bin/bash -le
echo -e "[32m
#######################################
# DockerStart
#######################################
[m"

chmod -R 777 $WORKSPACE/build.sh && \
chmod -R 777 $WORKSPACE/step.sh && \
chmod -R 777 $WORKSPACE/notify.sh && \
chmod -R 777 $WORKSPACE/link.sh
DOCKER_RESULT=0
source ./link.sh

docker run --rm -v $WORKSPACE:/home/worker/workspace -w /home/worker/workspace $LINK_1 $LINK_2 $LINK_3 -u worker -t aozora0000/jenkins-ci-node:latest /bin/bash -l step.sh || DOCKER_RESULT=$?

source $WORKSPACE/notify.sh

rm -f $WORKSPACE/step.sh && \
rm -f $WORKSPACE/notify.sh && \
rm -f $WORKSPACE/build.sh && \
rm -rf /tmp/jenkins_message*
exit $DOCKER_RESULT