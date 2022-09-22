include:
  - shared.sqlite3

c++ toolchain repo:
  pkgrepo.managed:
    - name: ppa:ubuntu-toolchain-r/test
    - refresh_db: true

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

# TODO: get ovh/cds from github https://github.com/ovh/cds
# https://github.com/ovh/cds/releases/tag/0.50.0
# try this command
# curl -s https://api.github.com/repos/username/projectname/releases/latest | jq '.assets[] | select(.name|match("linux_amd64.tar.gz$")) | .browser_download_url'
