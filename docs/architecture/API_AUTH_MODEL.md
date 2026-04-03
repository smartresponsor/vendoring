# Vendoring API Authentication Model

## Canonical machine access seam

Vendoring's canonical machine-access model is:

- `VendorApiKeyService`
- `VendorApiKey`
- bearer-token vendor resolution through API keys

This is the intended authentication mechanism for API clients. Human login, session identity, SSO, and full user-auth lifecycle remain outside the Vendoring boundary.

## Current enforcement status

At the current repository stage:

- the canonical machine-access seam exists in domain and service code;
- API key issuance, rotation, revocation, and bearer-token validation exist;
- full runtime enforcement across the strongest HTTP transaction slice is not yet the primary guardrail.

The strongest immediate protection now enforced at the write-path level is:

- deterministic write-rate limiting for mutation endpoints;
- explicit `429` contract and `Retry-After` signaling;
- structured logging and correlation for rejected write attempts.

## Next enforcement step

The next auth-hardening step should make bearer-token enforcement first-class on public write endpoints, with:

- permission checks such as `write:transactions`;
- explicit `401` and `403` response contracts;
- behavioral tests for missing, invalid, and under-scoped tokens;
- runtime-backed persistence and lookup confidence for the canonical API key seam.

## Architectural constraint

Do not replace the API key seam with ad hoc endpoint-local shared-secret logic.

If stronger runtime authentication is introduced, it should converge on:

- canonical bearer-token validation via `VendorApiKeyService`;
- explicit permission checks;
- vendor-local authorization rules on top of that authentication result.
