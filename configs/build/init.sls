cmd.run:
    sudo add-apt-repository --yes ppa:ubuntu-toolchain-r/test
cmd.run:
    sudo apt-get update
cmd.run:
    sudo apt-get install --yes make cpp gcc-9 g++-9 libpcre++-dev zlib1g-dev
cmd.run:
    sudo apt-get install --yes sqlite3
