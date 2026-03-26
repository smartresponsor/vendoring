#!/bin/sh
set -e
php bin/console category:backup
zip -r category-backup.zip category-backup.ndjson report/category-backup-manifest.json
