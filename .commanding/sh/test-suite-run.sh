#!/bin/sh
php -v
if [ -f vendor/bin/phpunit ]; then
  vendor/bin/phpunit
else
  echo 'phpunit not installed, skipping'
fi
