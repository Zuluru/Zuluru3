#!/bin/sh
set -e

APP_DIR=/var/www/html
cd "$APP_DIR"

echo "[entrypoint] Ensuring writable directories exist..."
mkdir -p \
    tmp/cache/models \
    tmp/cache/persistent \
    tmp/cache/views \
    tmp/sessions \
    tmp/tests \
    logs

if [ ! -f vendor/autoload.php ]; then
    echo "[entrypoint] vendor/ not found -- installing PHP libraries with composer..."
    composer install --no-interaction --no-progress
else
    echo "[entrypoint] vendor/ present -- skipping composer install."
fi

echo "[entrypoint] Starting: $*"
exec "$@"
