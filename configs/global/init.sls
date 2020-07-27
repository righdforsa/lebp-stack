nginx:
  pkg.installed:
    - name: nginx
  service.running:
    - enable: True
    - reload: True
    - require:
      - pkg: nginx
      - service: php-fpm

/etc/nginx/sites-available/default:
  file.managed:
    - source: salt://global/files/default
    - watch_in:
      - service: nginx

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

/etc/php/7.4/fpm/php.ini:
  file.managed:
    - source: salt://global/files/php.ini
    - watch_in:
      - service: php-fpm

/var/www/html/hello.php:
  file.managed:
    - source: salt://global/files/hello.php
