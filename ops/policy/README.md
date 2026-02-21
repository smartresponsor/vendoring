Policy documents

This folder contains non-runtime policy documents.

- Files under ops/policy are not loaded by Symfony.
- Only runtime Symfony configuration should remain under config (routes/services/packages).

Purpose: keep the repository structure canonical and avoid mixing runtime configuration with planning/policy artifacts.
