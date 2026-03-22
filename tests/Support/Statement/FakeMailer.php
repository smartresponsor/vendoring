<?php

declare(strict_types=1);

namespace App\Tests\Support\Statement;

use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\RawMessage;

final class FakeMailer implements MailerInterface
{
    /** @var list<RawMessage> */
    private array $messages = [];

    public function __construct(private readonly bool $shouldThrow = false)
    {
    }

    public function send(RawMessage $message, ?Envelope $envelope = null): void
    {
        if ($this->shouldThrow) {
            throw new \RuntimeException('mailer transport failed');
        }

        $this->messages[] = $message;
    }

    /** @return list<RawMessage> */
    public function messages(): array
    {
        return $this->messages;
    }
}
