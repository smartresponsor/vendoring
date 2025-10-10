# Vendor Domain — Iter I–II (Core + Legal)

High-level package for SmartResponsor.

Includes:
- Entities: Vendor, VendorPassport, VendorDocument
- DTO: VendorCreateDTO, VendorUpdateDTO, VendorDocumentDTO
- Services: VendorService, VendorPassportService, VendorDocumentService
- Events: VendorCreatedEvent, VendorActivatedEvent, VendorVerifiedEvent, DocumentUploadedEvent
- Repositories
- Migrations: Version0001VendorCore, Version0002VendorLegal
- Tests: BasicSmokeTest.php

Conventions: strict_types, PSR-12, Doctrine Attributes.
