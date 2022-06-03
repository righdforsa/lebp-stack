# lebp-stack
Generic instance for web platform projects using Linux, Nginx, Bedrock, and PHP. Vagrant is used to provision a local dev VM and bootstrap it. Afterwards, Salt is launched for configuration. (In theory this makes the process more portable to a remote server or several.)

# how to customize:
create a configs repo separate from lebp-stack
check it out to /srv/project, where it will be automatically included as another source tree by the salt minion config
run highstate

# initial getting started commands
```
# git commands
git init lebp-stack
cd lebp-stack
git submodule add git@github.com:Expensify/Bedrock.git
# maybe not needed because Bedrock makefile inits mbedtls, but including what I actually did
cd Bedrock
git submodule update --init
cd ../

# Build bedrock
vagrant up
vagrant ssh -c "cd /srv/project/lebp-stack/Bedrock && make clean && make CC=gcc-9 all"
vagrant ssh -c "touch /var/tmp/bedrock.db && chmod 744 /var/tmp/bedrock.db"
vagrant ssh -c "/srv/project/lebp-stack/Bedrock/bedrock -db /var/tmp/bedrock.db" 
cat /etc/rc.local
/srv/project/lebp-stack/Bedrock/bedrock -db /var/tmp/bedrock.db -fork
```

Running as a submodule as a project:
Setting up:
 - cd lebp-stack/Bedrock && make CC=gcc-9 all
 - cd lebp-stack/Bedrock-PHP && composer install
 - cd lebp-stack/scripts && sudo ./place-bedrock.sh

# todo list:
~get bedrock to compile and run~

~add php folder~

update Vagrantfile to be idempotent/work from scratch
get bedrock php libs working
  - work on passing the right config to the constructor

# update hosts file
127.0.0.1 lebp-stack.dev

# trust cert 
Trust your certificate in macOS Keychain Access
you’ll need to tell your computer to trust the certificate authority since it’s not trusted by default.

Open Keychain Access
Highlight the System section on the left
Find the lebp-stack.dev cert, this is what I dragged and dropped onto Keychain Access > System
Navigate to your certificate and double click it
In the dropdown “When using this certificate” choose “Always trust“
Close the window to save your changes—this will prompt you for your administrator password

