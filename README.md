# lebp-stack
Generic instance for web platform projects using Linux, Nginx, Bedrock, and PHP. Vagrant is used to provision a local dev VM and bootstrap it. Afterwards, Salt is launched for configuration. (In theory this makes the process more portable to a remote server or several.)

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
vagrant ssh -c "cd /vagrant/Bedrock && make clean && make all"
vagrant ssh -c "touch /vagrant/Bedrock/bedrock.db && chmod 744 /vagrant/Bedrock/bedrock.db"
vagrant ssh -c "/vagrant/Bedrock/bedrock -db /vagrant/Bedrock/bedrock.db" 
```

# todo list:
get bedrock to compile and run
add php folder
update Vagrantfile to be idempotent/work from scratch

