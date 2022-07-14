#!/bin/bash
echo "Running salt highstate"
salt-call --local -l debug state.highstate
