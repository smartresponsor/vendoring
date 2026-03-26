#!/bin/sh
set -e
until nc -z pg 5432; do sleep 1; done
until nc -z mysql 3306; do sleep 1; done
