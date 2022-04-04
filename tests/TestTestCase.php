<?php

namespace TillProchaska\KirbyTestUtils\Tests;

use Kirby\Filesystem\Dir;
use TillProchaska\KirbyTestUtils\TestCase as BaseTestCase;

/**
 * @internal
 * @coversNothing
 */
class TestTestCase extends BaseTestCase
{
    protected const STORAGE_DIR = __DIR__.'/support/kirby/storage';

    protected function beforeKirbyInit(): void
    {
        if (Dir::exists(static::STORAGE_DIR)) {
            Dir::remove(static::STORAGE_DIR);
        }

        Dir::make(static::STORAGE_DIR);
    }

    protected function kirbyProps(): array
    {
        return [
            'roots' => [
                'site' => __DIR__.'/support/kirby/site',
                'content' => static::STORAGE_DIR.'/content',
                'accounts' => static::STORAGE_DIR.'/accounts',
            ],
            'options' => [
                'defined-during-initialization' => 'Hello World!',
            ],
        ];
    }
}
