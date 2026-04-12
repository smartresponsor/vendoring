<?php

declare(strict_types=1);

namespace App\Entity;

/**
 * @noinspection PhpPropertyNamingConventionInspection
 */
final class CategoryHtmlBlock
{
    private string $id;
    private bool $published = false;

    public function __construct(string $categoryId, string $html)
    {
        $this->id = sha1($categoryId . '|' . $html);
    }

    public function publish(): void
    {
        $this->published = true;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function published(): bool
    {
        return $this->published;
    }
}
