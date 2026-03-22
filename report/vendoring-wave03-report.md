# Vendoring wave03 report

Base: cumulative snapshot wave02

## Applied change

Moved interface file to path-consistent canonical location:

- from `src/ServiceInterface/Vendor/Service/Payout/PayoutProviderBridgeInterface.php`
- to `src/ServiceInterface/Vendor/Payout/PayoutProviderBridgeInterface.php`

## Reason

The previous path duplicated the layer segment `Service` inside the `ServiceInterface` tree.
The class namespace was already `App\ServiceInterface\Vendor\Payout`, so the old location was a structural path anomaly.

## Safety

- No PHP code semantics changed.
- No namespace changed.
- No service wiring changed.
- Only filesystem location was normalized to match the declared namespace.
