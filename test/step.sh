#!/bin/bash -lxe
if [ -f ~/.bashrc ] ; then
    . ~/.bashrc
fi
echo -e "[32m
# ScriptStart 2 Steps
[m"
echo -e "[32m
## Step: 1/2 npmインストール
[m"

npm install

echo -e "[32m
## Step: 2/2 gulp起動
[m"

./node_modules/.bin/gulp test

