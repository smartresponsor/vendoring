# Vendoring Wave 59 — transaction doctrine foundation

## Scope
This wave strengthens the `VendorTransaction` persistence surface so the transaction flow no longer relies on an unmapped plain PHP object while Doctrine ORM is already part of the runtime stack.

## Applied changes
- introduced Doctrine ORM attribute mapping on `App\Entity\Vendor\VendorTransaction`
- bound the entity to `App\Repository\VendorTransactionRepository`
- formalized `App\EntityInterface\VendorTransactionInterface`
- added `createdAt` contract getter on the transaction entity interface
- tightened repository ordering for vendor transaction listing to newest-first
- added unit coverage for Doctrine mapping and repository ordering contract
- added transaction-doctrine smoke coverage and Composer script

## Result
The transaction flow now has a concrete minimal Doctrine entity contract instead of a plain unmapped object sitting behind Doctrine repositories and entity manager usage.
