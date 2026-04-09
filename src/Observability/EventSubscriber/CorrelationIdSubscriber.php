<?php

declare(strict_types=1);

namespace App\Observability\EventSubscriber;

use App\ServiceInterface\Observability\CorrelationContextInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Uid\Uuid;

/**
 * HTTP boundary subscriber that establishes and returns the canonical correlation identifier.
 *
 * The subscriber reads `X-Correlation-ID` from inbound requests, generates one when absent,
 * stores it in request attributes and correlation context, and mirrors the final value back
 * into response headers for operational tracing.
 */
final class CorrelationIdSubscriber implements EventSubscriberInterface
{
    private const string HEADER_NAME = 'X-Correlation-ID';
    private const string ATTRIBUTE_NAME = '_vendoring_correlation_id';

    public function __construct(private readonly CorrelationContextInterface $correlationContext)
    {
    }

    /**
     * Return the kernel events used to establish correlation context.
     *
     * @return array<string, array{0:string,1:int}> event map for request and response phases
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 256],
            KernelEvents::RESPONSE => ['onKernelResponse', -256],
        ];
    }

    /**
     * Resolve and persist the active correlation identifier for the main request.
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $correlationId = $this->resolveCorrelationId($request);

        $request->attributes->set(self::ATTRIBUTE_NAME, $correlationId);
        $this->correlationContext->beginRequest($correlationId);
    }

    /**
     * Mirror the active correlation identifier into the outbound HTTP response.
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $correlationId = $request->attributes->get(self::ATTRIBUTE_NAME);

        if (!is_string($correlationId) || '' === trim($correlationId)) {
            $correlationId = $this->correlationContext->currentCorrelationId() ?? $this->generateCorrelationId();
        }

        $event->getResponse()->headers->set(self::HEADER_NAME, $correlationId);
    }

    /**
     * Resolve the inbound correlation identifier from the request or generate a new one.
     */
    private function resolveCorrelationId(Request $request): string
    {
        $headerValue = $request->headers->get(self::HEADER_NAME);

        if (is_string($headerValue) && '' !== trim($headerValue)) {
            return trim($headerValue);
        }

        return $this->generateCorrelationId();
    }

    /**
     * Generate one RFC 4122 correlation identifier.
     */
    private function generateCorrelationId(): string
    {
        return Uuid::v7()->toRfc4122();
    }
}
