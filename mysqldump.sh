#!/bin/bash

# See http://stackoverflow.com/questions/59895/can-a-bash-script-tell-what-directory-its-stored-in/23905052#23905052
ROOT=$(readlink -f $(dirname "$0"))

cd $ROOT

# Get database parameters
dbname=$(grep "database_name" ./app/config/parameters.yml | cut -d " " -f 6)
dbuser=$(grep "database_user" ./app/config/parameters.yml | cut -d " " -f 6)
dbpassword=$(grep "database_password" ./app/config/parameters.yml | cut -d " " -f 6)

filename="$(date '+%Y-%m-%d_%H-%M-%S').sql"

echo "Export $dbname database"

mysqldump -B "$dbname" -u "$dbuser" --password="$dbpassword" > "$filename"

echo "Output file :"

ls -lh "$filename"
