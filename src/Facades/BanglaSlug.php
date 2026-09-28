<?php

declare(strict_types=1);

namespace AmdadulHaq\BanglaSlug\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string generate(string $text, string $separator = '-')
 * @method static string unique(string $text, (\Closure(string): bool)|null $exists = null)
 *
 * @see \AmdadulHaq\BanglaSlug\BanglaSlug
 */
class BanglaSlug extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \AmdadulHaq\BanglaSlug\BanglaSlug::class;
    }
}
