#!/bin/bash
set -e # Exit immediately if any command fails

echo "Starting custom entrypoint.sh..." >&2

# Change to the application directory
cd /var/www/html

# Load secrets from AWS Secrets Manager
echo "Loading secrets from AWS Secrets Manager..." >&2
php /var/www/html/load_secrets.php

# Check if secrets loading was successful
if [ $? -ne 0 ]; then
    echo "ERROR: load_secrets.php failed. Exiting." >&2
    exit 1
fi
echo "Secrets loaded successfully." >&2

# Clear Laravel configuration cache (critical after loading new environment variables)
echo "Clearing Laravel configuration cache..." >&2
php artisan optimize:clear || true

# Hand over control to the main Docker command (e.g., apache2-foreground)
exec "$@"
