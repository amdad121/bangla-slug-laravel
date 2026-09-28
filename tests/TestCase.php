<?php

declare(strict_types=1);

namespace AmdadulHaq\BanglaSlug\Tests;

use AmdadulHaq\BanglaSlug\BanglaSlugServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            BanglaSlugServiceProvider::class,
        ];
    }
}
