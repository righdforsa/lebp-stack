include:
  - global
  - shared.sqlite3
  - db.overlay # include the overlay states from the project repo

libpcrecpp0v5:
  pkg.installed

/var/bedrock:
  file.directory

# manage permissions on the db file
/var/bedrock/bedrock.db:
  file.managed:
    - create: false
    - replace: false
    - mode: 0600
    - require:
      - file: /var/bedrock

# TODO: replace with a "pkg" require once build server config does its job
test bedrock exists:
  cmd.run:
    - name: test -f /usr/sbin/bedrock

run bedrock:
  cmd.run:
    - name: /usr/sbin/bedrock -db /var/bedrock/bedrock.db -live -fork
    #- name: touch /var/bedrock/bedrock.db
    - unless: pgrep -x bedrock
    - require:
      - cmd: test bedrock exists
      - file: /var/bedrock/bedrock.db
      - pkg: libpcrecpp0v5
      - cmd: provision bedrock db

provision bedrock db:
  cmd.run:
    - name: sqlite3 /var/bedrock/bedrock.db "CREATE TABLE IF NOT EXISTS example (exampleID INTEGER PRIMARY KEY, created TEXT NOT NULL, details TEXT);"
    - require:
      - pkg: sqlite3
      - file: /var/bedrock/bedrock.db

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
