#!/bin/bash
touch /var/tmp/main.db
/usr/sbin/bedrock -db /var/tmp/main.db -v -fork

sleep 2
echo -ne 'status\r\n\r\n' | nc -N localhost 8888
