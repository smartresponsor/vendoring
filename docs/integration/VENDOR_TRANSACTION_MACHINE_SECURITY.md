# Host Application: transaction machine authentication

This candidate includes a standalone Symfony Security firewall and the matcher/authenticator services. The host owns its own security configuration. Do not import the standalone bootstrap firewall into a host.

After the Vendoring dependency is synchronized to this candidate, integrate the following three entries in the existing host config/packages/security.yaml, preserving all existing rules and ordering. Add the provider under the existing providers map:

    vendoring_machine_users:
        memory: ~

Insert the following firewall **before main**, after the existing dev and public firewalls:

    vendoring_transaction_machine:
        request_matcher: App\Vendoring\Security\VendorTransactionMachineRequestMatcher
        stateless: true
        provider: vendoring_machine_users
        custom_authenticators:
            - App\Vendoring\Authenticator\VendorTransactionMachineAuthenticator
        entry_point: App\Vendoring\Authenticator\VendorTransactionMachineAuthenticator

Insert the following access-control entry **before every broader matching rule**:

    - { request_matcher: App\Vendoring\Security\VendorTransactionMachineRequestMatcher, roles: ROLE_VENDOR_MACHINE }

In config/routes/00_vendoring_mobile.yaml, the existing vendoring_mobile_vendor_transaction route must use:

    path: /api/vendor/transaction/list/{id}
    controller: App\Vendoring\Service\Transaction\VendorTransactionHttpService::listByVendor
    methods: [GET]
    requirements:
        id: '[1-9][0-9]*'

Remove the obsolete explicit Service\Vendor\Transaction\VendorTransactionHttpService registration or replace it with the exact canonical Service\Transaction\VendorTransactionHttpService class. The bundle already exports the canonical service; do not register a second divergent implementation. Import only the explicit machine transaction create, list and two status-update routes if the host is not already importing them; verify the actual winning route/controller with debug:router and router:match. Do not assume that Cruding's generic route is equivalent to the explicit JSON route.

The matcher combines controller, method and canonical path. No blanket public access, security:false override, human-role inheritance, or bearer token exchange is introduced. The stateless authenticator resolves an active persisted Vendor using the existing API-key service. The transaction service independently checks the unchanged Authorization header, operation permission, canonical owner identifier and persisted owner association. A valid human mobile token is not a vendor API key. The host's Accessing authentication continues to own all other routes, including human UI and mobile account endpoints. Do not issue or embed machine keys in human mobile clients; a future human-to-vendor authorization flow needs a verified persisted human assignment and explicit capability checks.

Before deployment, run host DI compilation and actual HTTP tests against a disposable database: missing/malformed/foreign/revoked token 401, insufficient permission or mismatched explicit owner 403, missing or unowned transaction 404, authorized owner list/update 200, create 201, and noncanonical path 404. Confirm human authentication on unrelated routes and that the vendor principal cannot access human routes. Verify proxy preservation of Authorization; never log the token. Confirm no session reuse between requests.

Scope limitation: this integration covers only explicit transaction JSON endpoints. The operator form routes and other financial/profile endpoints are not switched to this machine firewall. They need their own verified authentication design; broadening this matcher is prohibited without reviewing each endpoint. The canonical Host checkout was not patched.

## Pass 8 isolated acceptance (2026-11-26)

A private Host copy at baseline f9a7b83, including tracked local edits, booted with the Vendoring candidate. Host DI lint and route matching passed. Fifteen in-process Symfony Kernel HTTP scenarios passed against a fresh disposable SQLite database: absent, unknown, revoked and human tokens rejected on machine endpoints; owner list and create accepted; cross-vendor read and read-only write denied; invalid status transition rejected; authorization and settlement accepted; missing transaction rejected; no machine session reuse; machine key rejected on Accessing session endpoint; real persisted human bearer session accepted.

Host integration prerequisites discovered by the test:

- Resolve Ordering's relative configuration import against the selected Vendoring candidate, not a canonical sibling checkout. Reconcile stale Vendoring class references in the Host configuration, controllers, normalizer and Twig extension.
- Use the actual Accessing entity class App\Accessing\Entity\Access\AccessEntity.
- Preserve Taxating's HMAC subscriber. Configure a real secret through the deployment's existing secret mechanism, and supply the TaxationNonceEntity Doctrine mapping. Tests used a random isolated key, real signed requests and nonce persistence; no production secret was read.
- Preserve native session support for human authentication. The isolated session save path was writable and private.
- PostgreSQL mappings, real reverse proxy header propagation and network-level HTTP acceptance remain distinct from this SQLite kernel test.

No canonical Host changes, live database access or deployment were performed.
