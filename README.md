# Vendor — Phase P43: Tenant Self‑Service Management API
One fast-import commit. Import, then run the smoke script.

## Import
```bash
unzip 194_vendor-phase-43-tenant-selfservice-api.zip -d vendor_p43 && cd vendor_p43
chmod +x import_vendor_phase.sh scripts/tenant-selfservice-api_smoke.sh
./import_vendor_phase.sh
./scripts/tenant-selfservice-api_smoke.sh
```
