# deploy/docker

Local container runtime for the vendoring component.

## Start

```bash
docker compose up --build -d
docker compose ps
curl http://127.0.0.1:18081/healthz
```

## Fixture dry-run in Docker

```bash
docker compose exec app composer fixtures:dry-run
```

## Stop

```bash
docker compose down --remove-orphans
```
