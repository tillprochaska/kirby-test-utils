<?php

namespace TillProchaska\KirbyTestUtils\Tests;

use TillProchaska\KirbyTestUtils\TestCase as BaseTestCase;

/**
 * @internal
 * @coversNothing
 */
class TestTestCase extends BaseTestCase
{
    protected function kirbyProps(): array
    {
        return [
            'roots' => [
                'config' => __DIR__.'/support/kirby/config',
            ],
        ];
    }
}
