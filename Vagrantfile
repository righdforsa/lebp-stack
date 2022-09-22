# -*- mode: ruby -*-
# vi: set ft=ruby :

# All Vagrant configuration is done below. The "2" in Vagrant.configure
# configures the configuration version (we support older styles for
# backwards compatibility). Please don't change it unless you know what
# you're doing.

Vagrant.configure(2) do |config|
  config.vm.box = "ubuntu/focal64"

  config.vm.hostname = "lebp-stack.dev"
  config.vm.network "private_network", ip: "10.2.2.3"
  config.vm.network "forwarded_port", guest: 443, host: 8443

  config.vm.provider "virtualbox" do |vb|
    vb.memory = "2048"
    vb.cpus = "1"
    vb.customize [ "guestproperty", "set", :id, "/VirtualBox/GuestAdd/VBoxService/--timesync-set-threshold", 10000 ]
  end

  config.ssh.forward_agent = true
  config.vm.synced_folder "../", "/srv/project/"

  config.vm.provision "shell", path: "scripts/env_prep.sh"

  # running salt the first time takes about 5 mins
  config.vm.provision "shell", path: "scripts/run-salt.sh"

  # building bedrock the first time is tremendously time-consuming, making it a manual step
  #config.vm.provision "shell", path: "scripts/build-bedrock.sh"

end
