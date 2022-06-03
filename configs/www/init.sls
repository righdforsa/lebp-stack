include:
  - www.overlay # include the overlay states from the project repo

# general
www /opt/SECRET:
  file.recurse:
    - name: /opt/SECRET
    - source: salt://www/files/SECRET
    - makedirs: true
    - exclude_pat: certs

# PHP
ondrej php repo:
  pkgrepo.managed:
    - name: deb http://ppa.launchpad.net/ondrej/php/ubuntu bionic main
    - ppa: ondrej/php

php-fpm:
  pkg.installed:
    - require:
      - pkgrepo: ondrej php repo
  service.running:
    - name: php8.1-fpm
    - enable: True
    - reload: True
    - require:
      - pkg: php-fpm

php-apcu:
  pkg.installed:
    - require:
      - pkg: php-fpm

php8.1-sqlite3:
  pkg.installed:
    - require:
      - pkg: php-fpm

php8.1-curl:
  pkg.installed:
    - require:
      - pkg: php-fpm

php8.1-simplexml:
  pkg.installed:
    - require:
      - pkg: php-fpm

/etc/php/8.1/fpm/php.ini:
  file.managed:
    - source: salt://www/files/php.ini
    - watch_in:
      - service: php-fpm

/var/www/html/api/hello.php:
  file.managed:
    - source: salt://www/files/hello.php

# NGINX
nginx:
  pkg.installed:
    - name: nginx
  service.running:
    - enable: True
    - reload: True
    - require:
      - pkg: nginx
      - service: php-fpm

/etc/nginx/nginx.conf:
  file.managed:
    - source: salt://www/files/nginx.conf
    - watch_in:
      - service: nginx

# site configs
/etc/nginx/sites-available/lebp-stack.dev:
  file.managed:
    - source: salt://www/files/lebp-stack.dev
    - watch_in:
      - service: nginx

# install certbot for managing letsencrypt certs
certbot installed:
  cmd.run:
    - name: snap install certbot --classic
    - unless: snap list certbot

#sudo ln -s /snap/bin/certbot /usr/bin/certbot
/usr/bin/certbot:
  file.symlink:
    - target: /snap/bin/certbot

# drop certs in the secret dir if you don't want to run letsencrypt certbot
www certs:
  file.recurse:
    - name: /etc/nginx/
    - source: salt://www/files/SECRET/certs
    - watch_in:
      - service: nginx
    - require:
      - pkg: nginx

# certs for local development
/etc/nginx/lebp-stack.dev.crt:
  file.managed:
    - source: salt://www/files/DEV/lebp-stack.dev.crt
    - watch_in:
      - service: nginx
    - require:
      - pkg: nginx

/etc/nginx/lebp-stack.dev.key:
  file.managed:
    - source: salt://www/files/DEV/lebp-stack.dev.key
    - watch_in:
      - service: nginx
    - require:
      - pkg: nginx

# PHP CLIENT
composer:
  pkg.installed

get composer:
  cmd.run:
    - name: curl -sS https://getcomposer.org/installer | php

/var/www/html:
  file.directory

/var/www/html/api:
  file.directory:
    - require:
      - file: /var/www/html

/var/www/html/Bedrock-PHP:
  file.symlink:
    - name: /var/www/html/Bedrock-PHP
{%- if 'this is a fake conditional' == 'holding space for local development options' %}
    - target: /vagrant/Bedrock-PHP
{%- else %}
    - target: /srv/Bedrock-PHP
{%- endif %}
    - require:
      - file: /var/www/html/api

/var/www/html/api/test_bedrock.php:
  file.managed:
    - source: salt://www/files/test_bedrock.php
    - require:
      - file: /var/www/html/api

/var/www/html/api/api.php:
  file.managed:
    - source: salt://www/files/api.php
    - require:
      - file: /var/www/html/api

/var/www/html/api/Command.php:
  file.managed:
    - source: salt://www/files/Command.php
    - require:
      - file: /var/www/html/api

/var/www/html/api/api_commands:
  file.recurse:
    - source: salt://www/files/api_commands
    - require:
      - file: /var/www/html/api

/var/www/html/api/api_lib:
  file.recurse:
    - source: salt://www/files/api_lib
    - require:
      - file: /var/www/html/api

/var/www/html/api/vendor:
  file.recurse:
    - source: salt://www/files/vendor
    - require:
      - file: /var/www/html/api

