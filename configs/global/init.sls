# system
/var/log/syslog:
  file.managed:
    - mode: 644

/opt/lebp-stack:
  file.directory

/opt/lebp-stack/bin:
  file.directory:
    - require:
      - file: /opt/lebp-stack
