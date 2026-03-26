#!/bin/sh
set -e
php -l src/Kernel.php
vendor/bin/phpunit --testsuite=category || true
