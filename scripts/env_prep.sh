#!/bin/bash

chmod +r /var/log/syslog
apt-get update
apt-get install --yes git
apt-get install --yes salt-common salt-minion
grep -q "- vagrant/configs/" /etc/salt/minion || \
    echo "file_roots:
  base:
    - /vagrant/configs/
" | tee -a /etc/salt/minion
salt-call --local state.highstate
