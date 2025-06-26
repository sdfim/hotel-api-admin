#!/bin/bash
set -e # Exit immediately if any command fails

echo "Starting custom entrypoint.sh..." >&2

# Change to the application directory
cd /var/www/html

# --- Step 1: Load secrets from AWS Secrets Manager ---
echo "Loading secrets from AWS Secrets Manager..." >&2
# This script will read APPNAME_FROM_ENV and AWS_REGION from App Runner's
# environment variables, and then fetch the content of the '{$appName}-envs' secret
# from AWS Secrets Manager to set those values as environment variables within the container.
php /var/www/html/load_secrets.php

# Check if the PHP script executed successfully (you can add more detailed checks)
if [ $? -ne 0 ]; then
    echo "ERROR: load_secrets.php failed. Exiting." >&2
    exit 1
fi
echo "Secrets loaded successfully." >&2
exec "$@"
