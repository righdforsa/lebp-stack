#!/bin/bash
DB_FILE="/var/bedrock/bedrock.db"

QUERY="$@"
sqlite3 -readonly $DB_FILE "$QUERY"

exit_code=$?
if [ $exit_code -ne 0 ]; then
    echo "Error running query, exit_code $exit_code"
    return $exit_code
fi

