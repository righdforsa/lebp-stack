#!/bin/bash

echo "Bootstrapping environment"
chmod +r /var/log/syslog

apt-get update
apt-get install --yes git

echo "Installing saltstack"
wget -O - https://repo.saltstack.com/py3/ubuntu/20.04/amd64/latest/SALTSTACK-GPG-KEY.pub | sudo apt-key add -
echo "deb http://repo.saltstack.com/py3/ubuntu/20.04/amd64/latest focal main" | tee /etc/apt/sources.list.d/saltstack.list
apt-get update
apt-get install --yes python3-zmq python3-contextvars python3-croniter
apt-get install --yes salt-common salt-minion

# creating "project" directory for config overlays
test -d /srv/project || mkdir /srv/project

echo "Updating salt minion config location"
if [[ "$(hostname -f)" =~ "lebp-stack" ]]; then
    grep -q ' /vagrant/configs/' /etc/salt/minion || \
    echo "file_roots:
  base:
    - /srv/project/configs_overlay/
    - /vagrant/configs/
" | tee -a /etc/salt/minion
else
    grep -q ' /srv/project/' /etc/salt/minion || \
    echo "file_roots:
  base:
    - /srv/project/configs_overlay/
    - /srv/salt/
" | tee -a /etc/salt/minion
fi

