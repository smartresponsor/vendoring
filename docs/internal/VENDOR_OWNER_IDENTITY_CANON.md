# Vendor Owner Identity Canon

- Vendor is the business root aggregate inside Vendoring.
- User is an external identity actor and is not modeled as a local entity here.
- ownerUserId is the primary ownership reference on Vendor.
- VendorUserAssignment is the local ownership/access bridge for vendor-to-user links.
- Credentials, password hashes, and login state remain outside Vendoring.
- Primary owner assignment must match Vendor.ownerUserId.
- Direct ORM relation from Vendoring to an external User aggregate is forbidden.
