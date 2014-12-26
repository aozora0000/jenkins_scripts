#!/bin/bash -l
RED=31
GREEN=32
YELLOW=33
BLUE=34

YAML_PARSER_URL=https://raw.githubusercontent.com/aozora0000/jenkins_scripts/master/YamlParser
HIPCHAT_NOTIFY=https://raw.githubusercontent.com/aozora0000/jenkins_scripts/master/hipchat_notify.sh
IDOBATA_NOTIFY=https://raw.githubusercontent.com/aozora0000/jenkins_scripts/master/idobata_notify.sh
IRC_NOTIFY=https://raw.githubusercontent.com/aozora0000/jenkins_scripts/master/irc_notify.sh

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
rev2 2014-12-26 irc-notify Deployment
rev1 2014-12-26 hipchat-notify & idobata-notify & yaml-parser Deployments
"

echo_c $GREEN "
#############################################
# Script Updater Start
#############################################
"
rm -f /usr/bin/yaml-parser    && curl $CURL_OPTION /usr/bin/yaml-parser    $YAML_PARSER_URL && chmod 0777 /usr/bin/yaml-parser    && \
rm -f /usr/bin/hipchat-notify && curl $CURL_OPTION /usr/bin/hipchat-notify $HIPCHAT_NOTIFY  && chmod 0777 /usr/bin/hipchat-notify && \
rm -f /usr/bin/idobata-notify && curl $CURL_OPTION /usr/bin/idobata-notify $IDOBATA_NOTIFY  && chmod 0777 /usr/bin/idobata-notify && \
rm -f /usr/bin/irc-notify     && curl $CURL_OPTION /usr/bin/irc-notify     $IRC_NOTIFY      && chmod 0777 /usr/bin/irc-notify

echo_c $GREEN "
#############################################
# Script Updater End
#############################################
"
