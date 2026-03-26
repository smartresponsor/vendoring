#!/bin/sh
php -r "file_put_contents('report/category-cache-metrics.json', json_encode(['ts'=>date('c'),'hits'=>[],'misses'=>[]], JSON_PRETTY_PRINT));"
