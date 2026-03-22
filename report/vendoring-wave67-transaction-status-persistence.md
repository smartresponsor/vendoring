# Vendoring Wave 67 — Transaction Status Persistence

## What changed
- added canonical status catalog `VendorTransactionStatus`
- rewired `VendorTransactionStatusPolicy` to use canonical constants instead of scattered literals
- strengthened PostgreSQL and SQLite transaction migrations with status `CHECK` constraints
- aligned `VendorTransaction` default status with canonical status catalog
- added smoke and unit coverage for status persistence contract

## Why
The transaction status policy already existed at PHP level, but persistence still allowed arbitrary status strings. This wave closes that gap so policy and schema enforce the same bounded status surface.
