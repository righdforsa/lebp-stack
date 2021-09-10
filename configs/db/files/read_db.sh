#!/bin/bash
DB_FILE="/var/bedrock/bedrock.db"

QUERY="$@"
sqlite3 -readonly $DB_FILE "$QUERY"
