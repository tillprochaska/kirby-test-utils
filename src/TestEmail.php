<?php

namespace TillProchaska\KirbyTestUtils;

use Kirby\Email\Email;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Collection;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;

class TestEmail extends Email
{
    public static $debug = true;

    public function __construct(array $props)
    {
        return parent::__construct($props, true);
    }

    public static function emails(): Collection
    {
        return new Collection(static::$emails);
    }

    public static function flushEmails(): void
    {
        static::$emails = [];
    }

    public function assertFrom(string $from): self
    {
        Assert::assertSame($from, $this->from());

        return $this;
    }

    public function assertTo(string|array $to): self
    {
        $to = A::wrap($to);
        $actual = array_keys($this->to());

        sort($to);
        sort($actual);

        Assert::assertSame($to, $actual);

        return $this;
    }

    public function assertSubject(string $subject): self
    {
        Assert::assertSame($subject, $this->subject());

        return $this;
    }

    public function assertBody(string $body): self
    {
        Assert::assertSame($body, $this->body()->text());

        return $this;
    }

    public function assertProperties(array $props): self
    {
        if (array_key_exists('from', $props)) {
            $this->assertFrom($props['from']);
        }

        if (array_key_exists('to', $props)) {
            $this->assertTo($props['to']);
        }

        if (array_key_exists('subject', $props)) {
            $this->assertSubject($props['subject']);
        }

        if (array_key_exists('body', $props)) {
            $this->assertBody($props['body']);
        }

        return $this;
    }

    public static function assertSent(array $props): void
    {
        $emails = static::emails()->filter(function ($email) use ($props) {
            try {
                $email->assertProperties($props);
            } catch (AssertionFailedError) {
                return false;
            }

            return true;
        });

        $count = $emails->count();

        if ($count <= 0) {
            Assert::fail('Expected to have sent exactly one email, but sent none.');
        }

        if ($count > 1) {
            Assert::fail("Expected to have sent exactly one email, but sent {$count}.");
        }
    }
}
