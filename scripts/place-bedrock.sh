#!/bin/bash

if [[ "$(hostname -f)" =~ "lebp-stack" ]]; then
    cp /vagrant/Bedrock/bedrock /usr/sbin
else
    latest=$(ls -trd ~/lebp-stack* | tail -1)
    cp ~/$latest/Bedrock/bedrock /usr/sbin
fi
