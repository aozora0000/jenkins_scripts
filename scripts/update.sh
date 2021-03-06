#!/bin/bash -l
RED=31
GREEN=32
YELLOW=33
BLUE=34

JQ_URL=http://stedolan.github.io/jq/download/linux64/jq
YAML_PARSER_URL=https://raw.githubusercontent.com/aozora0000/jenkins_scripts/master/scripts/yaml-parser
HIPCHAT_NOTIFY=https://raw.githubusercontent.com/aozora0000/jenkins_scripts/master/scripts/hipchat_notify.sh
IDOBATA_NOTIFY=https://raw.githubusercontent.com/aozora0000/jenkins_scripts/master/scripts/idobata_notify.sh
IRC_NOTIFY=https://raw.githubusercontent.com/aozora0000/jenkins_scripts/master/scripts/irc_notify.sh
CHATWORK_NOTIFY=https://raw.githubusercontent.com/aozora0000/jenkins_scripts/master/scripts/chatwork_notify.sh

CURL_OPTION="-# --retry 3 --retry-delay 4 -L -o"

function echo_c {
    color=$1
    shift
    echo -e "\033[${color}m$@\033[m"
}

echo_c $BLUE "
#############################################
# UpdateHistory
#############################################
rev5 2015-01-13 del dockerbuilder && add linking container
rev4 2015-01-11 add dockertestbuilder(not use .jenkins.yml ver)
rev3 2015-01-01 requirement Jq command & chatwork-notify Deployment
rev2 2014-12-26 irc-notify Deployment
rev1 2014-12-26 hipchat-notify & idobata-notify & yaml-parser Deployments
"

if [ ! -e /usr/bin/jq ]; then
    echo_c $YELLOW "
#############################################
# JQ command Install
#############################################
"
    curl -o /usr/bin/jq $JQ_URL && chmod +x /usr/bin/jq
fi

echo_c $GREEN "
#############################################
# Script Updater Start
#############################################
"
rm -f /usr/bin/yaml-parser       && curl $CURL_OPTION /usr/bin/yaml-parser       $YAML_PARSER_URL     && chmod 0777 /usr/bin/yaml-parser     && \
rm -f /usr/bin/hipchat-notify    && curl $CURL_OPTION /usr/bin/hipchat-notify    $HIPCHAT_NOTIFY      && chmod 0777 /usr/bin/hipchat-notify  && \
rm -f /usr/bin/idobata-notify    && curl $CURL_OPTION /usr/bin/idobata-notify    $IDOBATA_NOTIFY      && chmod 0777 /usr/bin/idobata-notify  && \
rm -f /usr/bin/irc-notify        && curl $CURL_OPTION /usr/bin/irc-notify        $IRC_NOTIFY          && chmod 0777 /usr/bin/irc-notify      && \
rm -f /usr/bin/chatwork-notify   && curl $CURL_OPTION /usr/bin/chatwork-notify   $CHATWORK_NOTIFY     && chmod 0777 /usr/bin/chatwork-notify && \
rm -f /usr/bin/dockertestbuilder

echo_c $GREEN "
#############################################
# Script Updater End
#############################################
"
