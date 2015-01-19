#!/bin/bash -e

usage() {
cat << EOF
Usage: $0 -t <token>

This script will read from stdin and send the contents to the given room as
a system message.

OPTIONS:
-h Show this message
-t <token> API token
-o API host (idobata.io)
EOF
}

TOKEN=${IDOBATA_TOKEN:-}
HOST=${IDOBATA_HOST:-idobata.io}
while getopts “ht:c:o:” OPTION; do
    case $OPTION in
        h) usage; exit 1;;
        t) TOKEN=$OPTARG;;
        o) HOST=$OPTARG;;
        [?]) usage; exit;;
    esac
done

# check for required args
if [[ -z $TOKEN ]]; then
    usage
    exit 1
fi

# get input
INPUT=$(cat)

# replace end of line to br
INPUT=$(echo -n "${INPUT}" | sed "s/$/\<br\>/")

# urlencode with perl
INPUT=$(echo -n "${INPUT}" | perl -p -e 's/([^A-Za-z0-9])/sprintf("%%%02X", ord($1))/seg')

curl -sS \
-d "format=html&source=$INPUT" \
https://$HOST/hook/generic/$TOKEN
