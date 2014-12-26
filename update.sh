#!/bin/bash -l
RED=31
GREEN=32
YELLOW=33
BLUE=34

YAML_PARSER_URL=https://raw.githubusercontent.com/aozora0000/jenkins_scripts/master/YamlParser
HIPCHAT_NOTIFY=https://raw.githubusercontent.com/aozora0000/jenkins_scripts/master/hipchat_notify.sh
IDOBATA_NOTIFY=https://raw.githubusercontent.com/aozora0000/jenkins_scripts/master/idobata_notify.sh

CURL_OPTION="-# --retry 3 --retry-delay 4 -L -o"

function echo_c {
    color=$1
    shift
    echo -e "\033[${color}m$@\033[m"
}


echo_c $GREEN "
#############################################
# Script Updater Start
#############################################
"
curl $CURL_OPTION /usr/bin/yaml-parser    $YAML_PARSER_URL && chmod 0777 /usr/bin/yaml-parser    && \
curl $CURL_OPTION /usr/bin/hipchat-notify $HIPCHAT_NOTIFY  && chmod 0777 /usr/bin/hipchat-notify && \
curl $CURL_OPTION /usr/bin/idobata-notify $IDOBATA_NOTIFY  && chmod 0777 /usr/bin/idobata-notify

echo_c $GREEN "
#############################################
# Script Updater End
#############################################
"
