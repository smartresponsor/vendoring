# Vendoring API Documentation Canon

## Purpose

This document defines the API documentation contract for Vendoring HTTP endpoints.

Vendoring API documentation must be explicit enough for:
- operators
- internal integrators
- test authors
- machine agents
- release reviewers

The canonical API documentation surface is OpenAPI via NelmioApiDocBundle.

## Required endpoint documentation

Every documented endpoint must provide:

- summary
- description
- tag
- path parameters
- query parameters where applicable
- request body schema where applicable
- success responses
- error responses
- security/auth expectations where applicable

## Tagging canon

Endpoints should be grouped under stable tags:

- Transactions
- Payouts
- Statements
- Runtime Ops
- Security
- Profiles
- Ownership
- Integrations

Avoid overly granular tags unless the endpoint family is large enough to justify it.

## Error contract rule

Every endpoint with non-trivial failure modes must document the main error cases.

Examples:
- validation errors
- not found
- invalid transition
- permission denied
- malformed payload
- incomplete business state

Error payloads should be described consistently wherever the component already
uses canonical error codes.

## Request/response discipline

Document the real contract, not an aspirational one.

If a field is:
- required -> mark it required
- nullable -> mark it nullable
- enum-like -> describe accepted values
- canonicalized/normalized -> mention that behavior where useful

## Security/auth rule

If an endpoint is protected by API key, bearer token, or operator-only semantics,
the documentation must state that explicitly.

If an endpoint is public or intentionally unauthenticated, that should also be explicit.

## Docblock to API description rule

Controller docblocks should supply:
- endpoint intent
- business meaning
- important caveats

OpenAPI-specific attributes should supply:
- structured parameters
- response schema
- tags
- security metadata

## Anti-patterns

Forbidden:
- undocumented error responses
- undocumented auth expectations
- generic low-signal summaries like “Handle request”
- mismatch between DTO contract and documented contract

## Release-candidate expectation

Vendoring API documentation is RC-ready when:
- all external or operator-facing endpoints are documented
- the `/api/doc` surface is coherent
- request/response contracts match real code behavior
- business-critical failure modes are visible in the API docs
