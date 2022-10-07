# lebp-stack
Generic instance for web platform projects using Linux, Nginx, Bedrock, and PHP. Vagrant is used to provision a local dev VM and bootstrap it. Afterwards, Salt is launched for configuration. (In theory this makes the process more portable to a remote server or several.)

# Setup to run the first time
## Build bedrock (takes ~20m the first time)
```
vagrant up
vagrant ssh -c "/vagrant/scripts/build-bedrock.sh"
```

### To save setup time by avoiding building bedrock, download the bedrock binary from the latest lebp-stack release:
use the URL from the first command as the input to the second command
```
curl -vl 'https://github.com/righdforsa/lebp-stack/releases/download/v0.1/bedrock' 2>&1 | grep 'location: ' | awk '{ print $3 }'
curl -o bedrock "<url>"
sudo cp bedrock /usr/sbin/bedrock
sudo chmod 755 /usr/sbin/bedrock
```
* last confirmed working under libc package version 2.31-0ubuntu9.9 and libpcre runtime files from 2:8.44-2+ubuntu20.04.1+deb.sury.org+1 

## Get php vendor libs (installed in configs/www/files/vendor-lebp-stack)
```
vagrant ssh -c "cd /vagrant/configs/www/files && curl -sS https://getcomposer.org/installer | php"
vagrant ssh -c "cd /vagrant/configs/www/files && php composer.phar install"
```

## Get Bedrock-PHP vendor libs
(FYI Bedrock-PHP composer config is currently broken in mainline, but our submodule is pinned to specific commit with fix)
```
vagrant ssh -c "cd /vagrant/Bedrock-PHP && curl -sS https://getcomposer.org/installer | php"
vagrant ssh -c "cd /vagrant/Bedrock-PHP/ && php composer.phar update"
```

## place and launch all updated configs
```
vagrant ssh -c "/vagrant/scripts/run-salt.sh"
vagrant ssh -c "sudo service nginx restart"
```

## local test
```
vagrant ssh -c "curl -vk https://localhost/test_bedrock.php"
```

# how to customize:
create a configs repo separate from lebp-stack
check it out to /srv/project in the vm, where it will be automatically included as another source tree by the salt minion config
run `vagrant ssh -c "sudo salt-call --local state.highstate"`

# Chris' legacy notes, just for him:
## Build bedrock
```
vagrant up
vagrant ssh -c "cd /srv/project/lebp-stack/Bedrock && make clean && make CC=gcc-9 all"
vagrant ssh -c "touch /var/tmp/bedrock.db && chmod 744 /var/tmp/bedrock.db"
vagrant ssh -c "/srv/project/lebp-stack/Bedrock/bedrock -db /var/tmp/bedrock.db" 
cat /etc/rc.local
/srv/project/lebp-stack/Bedrock/bedrock -db /var/tmp/bedrock.db -fork
```

## Running as a submodule in a larger project:
Setting up:
 - cd lebp-stack/Bedrock && make CC=gcc-9 all
 - cd lebp-stack/Bedrock-PHP && php7.4 /usr/bin/composer install
 - cd lebp-stack/scripts && sudo ./place-bedrock.sh

## initial repo creation commands
```
# git commands
git init lebp-stack
cd lebp-stack
git submodule add git@github.com:Expensify/Bedrock.git
# maybe not needed because Bedrock makefile inits mbedtls, but including what I actually did
cd Bedrock
git submodule update --init
cd ../
```

## todo list:
~get bedrock to compile and run~

~add php folder~

update Vagrantfile to be idempotent/work from scratch
get bedrock php libs working
  - work on passing the right config to the constructor

## update hosts file with the host-reachable IP from inside the vagrant vm
10.2.2.3 lebp-stack.dev

## trust cert 
Trust your certificate in macOS Keychain Access
You’ll need to tell your computer to trust the certificate authority since it’s not trusted by default.

*TODO:* since this is a public repo and publicly available cert, telling your system to trust it is actually a pretty big security hole. We need to find a more reliable way to manage this that is workable for the community. (e.g. create a new cert during provisioning and save it in SECRET)

Open Keychain Access
Highlight the System section on the left
Open finder and navigate to the lebp-stack.dev cert in the lebp-stack repo
drag and drop onto Keychain Access > System
in Keychain Access, Navigate to your certificate and double click it
In the dropdown “When using this certificate” choose “Always trust“
Close the window to save your changes—this will prompt you for your administrator password

