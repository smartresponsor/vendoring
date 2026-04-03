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

final class CorrelationIdSubscriber implements EventSubscriberInterface
{
    private const HEADER_NAME = 'X-Correlation-ID';
    private const ATTRIBUTE_NAME = '_vendoring_correlation_id';

    public function __construct(private readonly CorrelationContextInterface $correlationContext)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 256],
            KernelEvents::RESPONSE => ['onKernelResponse', -256],
        ];
    }

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

    private function resolveCorrelationId(Request $request): string
    {
        $headerValue = $request->headers->get(self::HEADER_NAME);

        if (is_string($headerValue) && '' !== trim($headerValue)) {
            return trim($headerValue);
        }

        return $this->generateCorrelationId();
    }

    private function generateCorrelationId(): string
    {
        return Uuid::v7()->toRfc4122();
    }
}
