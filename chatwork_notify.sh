#!/bin/bash -e

usage() {
    cat << EOF
    Usage: $0 -t <token>

    This script will read from stdin and send the contents to the given room as
    a system message.

    OPTIONS:
    -h Show this message
    -t <token> API token
    -r room id
    -o API host (api.chatwork.com)
    EOF
}

TOKEN=${CHATWORK_TOKEN:-}
ROOM_ID=${CHATWORK_ROOM_ID:-}
HOST=${CHATWORK_HOST:-api.chatwork.com}
while getopts “ht:r:o:” OPTION; do
    case $OPTION in
        h) usage; exit 1;;
        t) TOKEN=$OPTARG;;
        r) ROOM_ID=$OPTARG;;
        o) HOST=$OPTARG;;
        [?]) usage; exit;;
    esac
done

# check for required args
if [[ -z $TOKEN ]] || [[ -z $ROOM_ID ]] ; then
    usage
    exit 1
fi

# get input
INPUT=$(cat)

# urlencode with perl
INPUT=$(printf "${INPUT}" | perl -p -e 's/([^A-Za-z0-9])/sprintf("%%%02X", ord($1))/seg')

curl -X POST -H "X-ChatWorkToken: ${TOKEN}" -d "body=${INPUT}" "https://${HOST}/v1/rooms/${ROOM_ID}/messages" | jq 'if select(has("errors")) then 1 else 0 end'
