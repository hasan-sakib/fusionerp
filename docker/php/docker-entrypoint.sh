#!/bin/sh
set -e

# Production: copy baked public assets into the shared volume that nginx reads from.
# The shared volume is mounted at /var/www/shared_public only in docker-compose.prod.yml.
# In local dev this directory doesn't exist, so the block is skipped entirely.
if [ -d /var/www/shared_public ]; then
    rm -rf /var/www/shared_public/*
    cp -r /var/www/html/public/. /var/www/shared_public/
fi

exec /usr/bin/supervisord -c /etc/supervisord.conf
