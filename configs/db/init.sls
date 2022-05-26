include:
  - global
  - db.overlay

libpcrecpp0v5:
  pkg.installed

/var/bedrock:
  file.directory

/var/bedrock/bedrock.db:
  file.managed

run bedrock:
  cmd.run:
    - name: sudo /usr/sbin/bedrock -db /var/bedrock/bedrock.db -live -fork
    - unless: pgrep -x bedrock

provision bedrock db:
  cmd.run:
    - name: sqlite3 /var/bedrock/bedrock.db "CREATE TABLE IF NOT EXISTS example (exampleID INTEGER PRIMARY KEY, created TEXT NOT NULL, details TEXT);"

/opt/lebp-stack/bin/read_db.sh:
  file.managed:
    - source: salt://db/files/read_db.sh
    - mode: 755
    - require:
      - file: /opt/lebp-stack/bin

/etc/cron.d:
  file.recurse:
    - source: salt://db/files/cron.d

db /opt/SECRET:
  file.recurse:
    - name: /opt/SECRET
    - source: salt://db/files/SECRET
    - makedirs: true
