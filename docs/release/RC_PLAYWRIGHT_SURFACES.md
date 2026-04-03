# Vendoring RC Playwright Surfaces

## Purpose

Playwright provides full HTTP + browser-level verification on top of Panther.

- Panther → internal kernel + contract confidence
- Playwright → external runtime + deployment confidence

## Coverage

### Browser
- /api/doc rendered
- HTML verified

### Runtime APIs
- runtime status endpoint
- release baseline endpoint

### Business flow
- transaction create
- transaction list
- transaction status update

### Contract edges
- duplicate transaction → 409
- malformed JSON → 400
- not found → 404

## Execution

```bash
npm install
npx playwright install
npm run test:e2e
```

## Runtime model

- Playwright starts Symfony via `composer server:run`
- base URL is dynamically resolved
- tests run against real HTTP server

## Result

This layer guarantees:

- full HTTP surface stability
- external system reachability
- production-like execution path
- CI-ready browser + API verification

