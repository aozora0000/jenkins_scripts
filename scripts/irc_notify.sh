#!/bin/bash -e

usage() {
cat << EOF
Usage: $0 -c \#CHANNEL -o http://localhost:4649

This script will read from stdin and send the contents to the given room as
a system message.

OPTIONS:
-h Show this message
-c CHANNEL
-o IKACHAN http://localhost:4979
EOF
}

CHANNEL=${CHANNEL:-}
IKACHAN=${IKACHAN:-}
while getopts “hc:o:” OPTION; do
    case $OPTION in
        h) usage; exit 1;;
        c) CHANNEL=$OPTARG;;
        o) IKACHAN=$OPTARG;;
        [?]) usage; exit;;
    esac
done

# check for required args
if [[ -z $CHANNEL ]] || [[ -z $IKACHAN ]]; then
    usage
    exit 1
fi

# get input
INPUT=$(cat)

curl -F channel=\#$CHANNEL $IKACHAN/join && \
curl -s -F channel=\#$CHANNEL -F message="$INPUT" $IKACHAN/notice
