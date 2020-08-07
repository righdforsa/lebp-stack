#!/bin/bash

echo "Bootstrapping environment"
chmod +r /var/log/syslog

echo "Installing saltstack"
apt-get update
apt-get install --yes git
apt-get install --yes salt-common salt-minion

echo "Updating salt minion config location"
grep -q "- vagrant/configs/" /etc/salt/minion || \
    echo "file_roots:
  base:
    - /vagrant/configs/
" | tee -a /etc/salt/minion

echo "Running salt highstate"
salt-call --local state.highstate
