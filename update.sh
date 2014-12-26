#!/bin/sh -l
RED=31
GREEN=32
YELLOW=33
BLUE=34

function echo_c {
    color=$1
    shift
    echo -e "\033[${color}m$@\033[m"
}

echo_c $RED "test"
