# Phase 65 — RBAC Authorization Matrix Enforcement

## Purpose

This phase turns vendor-local human/operator authorization into a canonical code contract.

The repository already had:
- ownership and assignment persistence
- API-key based machine authentication for write endpoints
- human assignment read models

What was still weak was the RBAC layer itself: roles were present, but the role set, capability set,
and repository-backed access decision rules were not enforced through one canonical matrix.

## Added contracts

### Canonical roles

`App\Vendoring\ValueObject\VendorRole` defines the allowed vendor-local roles:
- `owner`
- `operator`
- `finance`
- `viewer`

Unknown roles are rejected by assignment services.

### Canonical capability matrix

`App\Vendoring\ServiceInterface\Security\VendorAuthorizationMatrixInterface`
`App\Vendoring\Service\Security\VendorAuthorizationMatrix`

Canonical capabilities currently covered:
- `transactions.read`
- `transactions.write`
- `payouts.read`
- `payouts.write`
- `statements.read`
- `statements.send`
- `ownership.read`
- `ownership.write`

### Repository-backed access resolver

`App\Vendoring\ServiceInterface\Security\VendorAccessResolverInterface`
`App\Vendoring\Service\Security\VendorAccessResolver`

The resolver explains and answers questions of the form:
- does user X have capability Y for vendor Z?

## Runtime effect

- assignment writes now normalize and validate roles
- ownership projections now expose role-derived capabilities per active assignment
- authorization decisions can be reused by future controllers, operators, and admin pages

## Non-goals

This phase does **not** introduce:
- SSO
- session authentication
- UI login
- external IAM
- organization-wide ACLs outside the vendor-local bounded context

## Acceptance baseline

- canonical role registry exists
- canonical capability matrix exists
- repository-backed access resolver exists
- invalid roles are rejected
- ownership read models expose capabilities for active assignments
