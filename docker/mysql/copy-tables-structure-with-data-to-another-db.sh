#!/bin/bash

# MySQL connection details
MYSQL_USER="admin"
MYSQL_PASSWORD="mysqlsecretpasswd"
SOURCE_DB="ujv"
TARGET_DB="ujv_test"

# Connect to MySQL and retrieve table names from the source database
TABLES=$(mysql -u $MYSQL_USER -p$MYSQL_PASSWORD -N -B -e "USE $SOURCE_DB; SHOW TABLES;")

# Iterate over each table name
for TABLE in $TABLES; do
    # Generate the SQL statement to copy the table
    SQL="CREATE TABLE $TARGET_DB.$TABLE AS SELECT * FROM $SOURCE_DB.$TABLE;"

    # Execute the SQL statement
    mysql -u $MYSQL_USER -p$MYSQL_PASSWORD -e "$SQL"
done
