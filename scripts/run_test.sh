#!/bin/bash

# is nginx running?
if ( pgrep nginx >/dev/null ); then echo "nginx running"; else "nginx down" && exit 1; fi
if ( curl -f -s localhost/hello.php >/dev/null ); then echo "php is working"; else echo "php not working" && exit 2; fi
if ( pgrep bedrock >/dev/null ); then echo "bedrock running"; else echo "bedrock not running" && exit 3; fi
if ( echo "status\r\nconnection:close\r\n\r\n" | nc -N localhost 8888 ); then echo "bedrock working"; else echo "berock not working" && exit 4; fi
if ( curl -f -s localhost/test_bedrock.php ); then echo "bedrock client loading successfully"; else echo "bedrock client not working" && exit 5; fi
