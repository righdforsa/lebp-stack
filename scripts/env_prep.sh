#!/bin/bash

echo "Bootstrapping environment"
chmod +r /var/log/syslog

apt-get update
apt-get install --yes git

echo "Installing saltstack"
wget -O - https://repo.saltstack.com/py3/ubuntu/20.04/amd64/latest/SALTSTACK-GPG-KEY.pub | sudo apt-key add -
echo "deb http://repo.saltstack.com/py3/ubuntu/20.04/amd64/latest focal main" | tee /etc/apt/sources.list.d/saltstack.list
apt-get update
apt-get install --yes salt-common salt-minion

echo "Updating salt minion config location"
grep -q "- vagrant/configs/" /etc/salt/minion || \
    echo "file_roots:
  base:
    - /srv/project/configs_overlay/
    - /vagrant/configs/
" | tee -a /etc/salt/minion

echo "Running salt highstate"
salt-call --local state.highstate
