#!/bin/sh
set -e
LAG=${1:-0}
STATUS=green
if [ "$LAG" -gt 5 ]; then STATUS=red; fi
echo '{"lag_sec":'$LAG',"status":"'$STATUS'"}' > report/category-projection-lag.json
