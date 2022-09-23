#!/bin/bash
echo "Running salt highstate"
sudo salt-call --local -l debug state.highstate
