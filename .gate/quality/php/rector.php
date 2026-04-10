<?php

declare(strict_types=1);

return static function (object $rectorConfig): void {
    if (!method_exists($rectorConfig, 'paths')) {
        return;
    }

    $rectorConfig->paths([
        __DIR__.'/../../../src',
    ]);
};
