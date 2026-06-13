# Vendoring Wave 12C — HTTP read route implementation

Wave 12C implements only the Wave 12B `real_read_candidate` HTTP services.

## Implemented services

- `App\Vendoring\Service\Http\Vendor\VendorIndexService`
- `App\Vendoring\Service\Http\Vendor\VendorShowService`
- `App\Vendoring\Service\Http\Vendor\Attachment\Document\VendorAttachmentDocumentIndexService`
- `App\Vendoring\Service\Http\Vendor\Attachment\Document\VendorAttachmentDocumentShowService`
- `App\Vendoring\Service\Http\Vendor\Attachment\Media\VendorAttachmentMediaIndexService`
- `App\Vendoring\Service\Http\Vendor\Attachment\Media\VendorAttachmentMediaShowService`
- `App\Vendoring\Service\Http\Vendor\VendorHttpRouteResponseService`

## Runtime contract

The implemented services are intentionally repository-free. They return a deterministic read-route payload with:

- `status: read_route_ready`
- `persistence: quarantined`
- `mutationAllowed: false`
- empty `items` or `item: null`

This keeps the Cruding FQCN route surface executable without re-introducing the absent Vendoring `Entity`, `Repository`, `Projection`, `Event`, or `Policy` layers.

## Non-goals

Wave 12C does not:

- add controllers;
- restore Doctrine entities;
- restore repositories;
- enable write-side routes;
- implement payout, onboarding, commission, category assignment, or attachment mutation flows;
- remove Wave 11 quarantined services.

## Next step

Wave 12D canonizes the zero-controller surface and marks non-read skeleton services as blocked/inert explicitly.
