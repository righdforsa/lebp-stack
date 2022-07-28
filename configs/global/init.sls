# system
/var/log/syslog:
  file.managed:
    - mode: 644
    - replace: false

/opt/lebp-stack:
  file.directory

/opt/lebp-stack/bin:
  file.directory:
    - require:
      - file: /opt/lebp-stack

global helper pkgs:
  pkg.installed:
    - pkgs:
      - jq
