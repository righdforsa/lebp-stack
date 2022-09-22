#!/bin/bash

if [[ "$(hostname -f)" =~ "lebp-stack" ]]; then
    cd /srv/project/lebp-stack/Bedrock && make CC=gcc-9 -j8 all
    sudo cp /srv/project/lebp-stack/Bedrock/bedrock /usr/sbin
else
    latest=$(ls -trd ~/lebp-stack* | tail -1)
    cd ~/$latest/Bedrock && make CC=gcc-9 -j8 all
    sudo cp ~/$latest/Bedrock/bedrock /usr/sbin
fi
