<?php

use TillProchaska\KirbyTestUtils\TestEmail;

expect()->extend('toSee', function (string $string) {
    return $this->value->assertSee($string);
});

expect()->extend('toSeeText', function (string $text) {
    $this->value->assertSeeText($text);

    return $this;
});

expect()->extend('toHaveCode', function (int $code) {
    $this->value->assertCode($code);

    return $this;
});

expect()->extend('toHaveHeader', function (string $headerName, mixed $value = null) {
    $this->value->assertHeader($headerName, $value);

    return $this;
});

expect()->extend('toHaveSelector', function (string $selector, ?int $count = null, ?string $text = null, ?array $attributes = null) {
    $this->value->assertSelector($selector, $count, $text, $attributes);

    return $this;
});

expect()->extend('toSendEmail', function (array $props) {
    TestEmail::assertSent($props);

    return $this;
});
