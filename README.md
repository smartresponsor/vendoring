# Vendor Domain — Final

Полный пакет изолированных контрактов домена Vendor для SmartResponsor.

## Состав
- **Entities:** Vendor, VendorPassport, VendorDocument, VendorBilling, VendorLedgerBinding, VendorProfile, VendorMedia, VendorAttachment, VendorSecurity, VendorAnalytics
- **DTO:** VendorCreateDTO, VendorUpdateDTO, VendorDocumentDTO, VendorBillingDTO, VendorProfileDTO, VendorMediaUploadDTO, VendorAttachmentDTO
- **Services:** VendorService, VendorPassportService, VendorDocumentService, VendorBillingService, VendorProfileService, VendorMediaService
- **Events:** VendorCreatedEvent, VendorActivatedEvent, VendorVerifiedEvent, DocumentUploadedEvent, VendorPayoutRequestedEvent, VendorPayoutCompletedEvent, VendorProfileUpdatedEvent, VendorMediaUploadedEvent, VendorAttachmentUploadedEvent
- **Repositories:** для всех сущностей
- **Migrations:** Version0001..0005 (core, legal, billing/ledger, profile/media, security/analytics)
- **Tests:** BasicSmokeTest.php

## Принципы
- strict_types, PSR-12, Doctrine Attributes
- Изоляция контрактов: `src/Entity/Vendor/*` и симметрия слоёв
- Готовность к событиям и multitenancy
