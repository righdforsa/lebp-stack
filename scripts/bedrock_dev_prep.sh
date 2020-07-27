#!/bin/bash
#TODO: shsould move to salt
sudo add-apt-repository --yes ppa:ubuntu-toolchain-r/test
sudo apt-get update
sudo apt-get install --yes make cpp gcc-9 g++-9 libpcre++-dev zlib1g-dev
sudo apt-get install --yes sqlite3
