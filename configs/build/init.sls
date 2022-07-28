include:
  - shared.sqlite3

c++ toolchain repo:
  pkgrepo.managed:
    - name: ppa:ubuntu-toolchain-r/test
    - refresh_db: true

#cmd.run:
#    sudo add-apt-repository --yes ppa:ubuntu-toolchain-r/test

c++ build packages:
  pkg.installed:
    - pkgs:
      - make
      - cpp
      - gcc-9
      - g++-9
      - libpcre++-dev
      - zlib1g-dev
    - require:
      - pkgrepo: c++ toolchain repo

#cmd.run:
#    sudo apt-get install --yes make cpp gcc-9 g++-9 libpcre++-dev zlib1g-dev

#cmd.run:
#    sudo apt-get install --yes sqlite3

# TODO: get this to work. Throws git errors when trying to work with the mbedtls submodule
#build bedrock:
#  cmd.run:
#    - name: make CC=gcc-9 -j8 all
#    - cwd: /srv/project/lebp-stack/Bedrock
#    - require:
#      - pkg: c++ build packages

# TODO: get ovh/cds from github https://github.com/ovh/cds
# https://github.com/ovh/cds/releases/tag/0.50.0
# try this command
# curl -s https://api.github.com/repos/username/projectname/releases/latest | jq '.assets[] | select(.name|match("linux_amd64.tar.gz$")) | .browser_download_url'
