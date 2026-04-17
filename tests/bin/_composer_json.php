<?php

declare(strict_types=1);

/**
 * @return array<string, mixed>
 */
function vendoring_load_composer_json(string $root): array
{
    $composerPath = $root . '/composer.json';
    $decoded = json_decode((string) file_get_contents($composerPath), true, 512, JSON_THROW_ON_ERROR);

    if (!is_array($decoded)) {
        throw new RuntimeException('composer.json must decode to an associative array.');
    }

    if (array_is_list($decoded)) {
        throw new RuntimeException('composer.json must decode to an associative array.');
    }

    /** @var array<string, mixed> $decoded */
    return $decoded;
}

/**
 * @param array<string, mixed> $composer
 *
 * @return array<string, mixed>
 */
function vendoring_composer_section(array $composer, string $section): array
{
    $value = $composer[$section] ?? null;

    if (!is_array($value)) {
        return [];
    }

    if (array_is_list($value)) {
        return [];
    }

    /** @var array<string, mixed> $value */
    return $value;
}

/**
 * @return list<string>
 */
function vendoring_string_list(mixed $value): array
{
    if (!is_array($value)) {
        return [];
    }

    $result = [];

    foreach ($value as $item) {
        if (is_string($item)) {
            $result[] = $item;
        }
    }

    return $result;
}

/**
 * @param array<string, mixed> $composer
 *
 * @return array<string, mixed>
 */
function vendoring_composer_scripts(array $composer): array
{
    return vendoring_composer_section($composer, 'scripts');
}

/**
 * @param array<string, mixed> $composer
 */
function vendoring_has_script(array $composer, string $scriptName): bool
{
    $scripts = vendoring_composer_scripts($composer);

    return array_key_exists($scriptName, $scripts);
}

/**
 * @param array<string, mixed> $composer
 *
 * @return list<string>
 */
function vendoring_script_commands(array $composer, string $scriptName): array
{
    $scripts = vendoring_composer_scripts($composer);

    return vendoring_string_list($scripts[$scriptName] ?? null);
}

/**
 * @param Traversable<mixed, mixed>|array<mixed> $iterable
 *
 * @return list<SplFileInfo>
 */
function vendoring_php_files(iterable $iterable): array
{
    $result = [];

    foreach ($iterable as $item) {
        if (!$item instanceof SplFileInfo || !$item->isFile() || 'php' !== $item->getExtension()) {
            continue;
        }

        $result[] = $item;
    }

    return $result;
}

/**
 * @return array<string, mixed>
 */
function vendoring_decode_json_array(string $json): array
{
    $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

    if (!is_array($decoded)) {
        throw new RuntimeException('Expected JSON object/array payload.');
    }

    if (array_is_list($decoded)) {
        throw new RuntimeException('composer.json must decode to an associative array.');
    }

    /** @var array<string, mixed> $decoded */
    return $decoded;
}
