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
    - name: php7.4-fpm
    - enable: True
    - reload: True
    - require:
      - pkg: php-fpm

php-apcu:
  pkg.installed:
    - require:
      - pkg: php-fpm

/etc/php/7.4/fpm/php.ini:
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

/etc/nginx/sites-available/default:
  file.managed:
    - source: salt://www/files/default
    - watch_in:
      - service: nginx

# TODO: make these configs work
certbot:
  snap.installed

#sudo ln -s /snap/bin/certbot /usr/bin/certbot
/usr/bin/certbot:
  file.symlink:
    - target: /snap/bin/certbot

/etc/nginx/dev.theneighborhoodsquatch.com.pem:
  file.managed:
    - source: salt://www/files/dev.theneighborhoodsquatch.com.pem
    - watch_in:
      - service: nginx
    - require:
      - pkg: nginx

/etc/nginx/dev.theneighborhoodsquatch.com.privkey.pem:
  file.managed:
    - source: salt://www/files/dev.theneighborhoodsquatch.com.privkey.pem
    - watch_in:
      - service: nginx
    - require:
      - pkg: nginx

/etc/nginx/dev.theneighborhoodsquatch.com.chain.pem:
  file.managed:
    - source: salt://www/files/dev.theneighborhoodsquatch.com.chain.pem
    - watch_in:
      - service: nginx
    - require:
      - pkg: nginx

# PHP CLIENT
composer:
  pkg.installed

/var/www/html:
  file.directory

/var/www/html/api:
  file.directory:
    - require:
      - file: /var/www/html

/etc/nginx/lebp-stack.dev.crt:
  file.managed:
    - source: salt://www/files/lebp-stack.dev.crt
    - watch_in:
      - service: nginx
    - require:
      - pkg: nginx

/etc/nginx/lebp-stack.dev.key:
  file.managed:
    - source: salt://www/files/lebp-stack.dev.key
    - watch_in:
      - service: nginx
    - require:
      - pkg: nginx

/var/www/html/api/Bedrock-PHP:
  file.symlink:
    - name: /var/www/html/api/Bedrock-PHP
    - target: /vagrant/Bedrock-PHP
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