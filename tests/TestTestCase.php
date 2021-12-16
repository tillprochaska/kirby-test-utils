<?php

namespace TillProchaska\KirbyTestUtils\Tests;

use Kirby\Cms\App as Kirby;
use TillProchaska\KirbyTestUtils\TestCase as BaseTestCase;

/**
 * @internal
 * @coversNothing
 */
class TestTestCase extends BaseTestCase
{
    protected function initializeKirbyInstance(): Kirby
    {
        return new Kirby([
            'roots' => [
                'config' => __DIR__.'/support/kirby/config',
            ],
        ]);
    }
}
