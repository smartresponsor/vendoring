#!/usr/bin/env sh
set -eu

cd /app
mkdir -p var/cache var/log var/run
git config --global --add safe.directory /app

if [ ! -f vendor/autoload.php ]; then
  composer install --no-interaction --prefer-dist
fi

exec ./tools/local/server-run.sh
