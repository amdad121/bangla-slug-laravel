<?php

declare(strict_types=1);

namespace AmdadulHaq\BanglaSlug;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class BanglaSlugServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/bangla-slug.php', 'bangla-slug');

        $this->app->singleton(BanglaSlug::class, fn (Application $app): BanglaSlug => new BanglaSlug(
            words: $app->make(Repository::class)->get('bangla-slug.words', []),
            suffixes: $app->make(Repository::class)->get('bangla-slug.suffixes', []),
            maxLength: (int) $app->make(Repository::class)->get('bangla-slug.max_length', 80),
        ));
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/bangla-slug.php' => config_path('bangla-slug.php'),
        ], 'bangla-slug-config');

        Str::macro('banglaSlug', fn (string $text, string $separator = '-'): string => resolve(BanglaSlug::class)->generate($text, $separator));
    }
}
