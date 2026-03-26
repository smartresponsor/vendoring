#!/bin/sh
set -e
RESULT=report/category-smoke.json
API_OK=0; GQL_OK=0; ADMIN_OK=0
curl -sf http://localhost:8080/api/category/tree && API_OK=1 || true
curl -sf http://localhost:8080/graphql -d '{"query":"{categories{id}}"}' -H 'Content-Type: application/json' && GQL_OK=1 || true
curl -sf http://localhost:8080/admin/category && ADMIN_OK=1 || true
echo "{\"api\":$API_OK,\"graphql\":$GQL_OK,\"admin\":$ADMIN_OK}" > "$RESULT"
