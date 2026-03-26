#!/bin/sh
if [ -f vendor/bin/php-cs-fixer ]; then
  vendor/bin/php-cs-fixer fix --dry-run --diff
else
  echo 'php-cs-fixer not installed, skipping'
fi
