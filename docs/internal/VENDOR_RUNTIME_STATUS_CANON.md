# Vendor Runtime Status Canon

- Vendor runtime status is a vendor-local release-facing surface.
- It aggregates ownership, finance, statement delivery, and external integration runtime contours.
- It must not load or expose an external User aggregate.
- It must not trigger live external side effects.
- It is intended for admin/ops/status inspection, not for domain mutation.
