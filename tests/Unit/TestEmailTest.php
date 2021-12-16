<?php

use PHPUnit\Framework\AssertionFailedError;
use TillProchaska\KirbyTestUtils\TestEmail;

beforeEach(function () {
    TestEmail::flushEmails();

    $this->email = new TestEmail([
        'to' => ['john.doe@example.org', 'richard.roe@example.org'],
        'from' => 'jane.doe@example.org',
        'subject' => 'Hello World!',
        'body' => 'Lorem ipsum',
    ]);
});

it('stores sent emails', function () {
    expect(TestEmail::emails())->toHaveCount(1);
});

it('passes if it has specified sender', function () {
    expect($this->email->assertFrom('jane.doe@example.org'))->toEqual($this->email);
});

it('fails if it does not have specified sender', function () {
    $this->email->assertFrom('john.doe@example.org');
})->throws(AssertionFailedError::class);

it('passes if it has specified recipients', function () {
    expect($this->email->assertTo(['richard.roe@example.org', 'john.doe@example.org']))->toEqual($this->email);
    expect($this->email->assertTo(['john.doe@example.org', 'richard.roe@example.org']))->toEqual($this->email);
});

it('fails if it does not have specified recipients', function () {
    $this->email->assertTo('richard.roe@example.org');
})->throws(AssertionFailedError::class);

it('passes if it has specified subject', function () {
    expect($this->email->assertSubject('Hello World!'))->toEqual($this->email);
});

it('fails if it does not have specified subject', function () {
    $this->email->assertSubject('This is not the subject');
})->throws(AssertionFailedError::class);

it('passes if it has specified body', function () {
    expect($this->email->assertBody('Lorem ipsum'))->toEqual($this->email);
});

it('fails if it does not have specified body', function () {
    $this->email->assertBody('This is not the body');
})->throws(AssertionFailedError::class);

it('passes if it has all specified properties', function () {
    expect($this->email->assertProperties([
        'to' => ['john.doe@example.org', 'richard.roe@example.org'],
        'from' => 'jane.doe@example.org',
        'subject' => 'Hello World!',
        'body' => 'Lorem ipsum',
    ]))->toEqual($this->email);

    // Passes as well if only a subset of available properties are specified
    expect($this->email->assertProperties([
        'to' => ['john.doe@example.org', 'richard.roe@example.org'],
    ]))->toEqual($this->email);
});

it('fails if it does not have specified properties', function () {
    $this->email->assertProperties([
        'to' => 'richard.roe@example.org',
        'from' => 'jane.doe@example.org',
        'subject' => 'Hello World!',
    ]);
})->throws(AssertionFailedError::class);

it('passes if an email matching specified properties has been sent', function () {
    // Send another email that matches none of the properties
    // to ensure that a single email that matches the properties
    // is enough to pass the assertion
    new TestEmail([
        'from' => 'max.mustermann@example.org',
        'to' => 'beate.beispiel@example.org',
        'subject' => 'Hallo Welt!',
        'body' => 'Mustertext',
    ]);

    TestEmail::assertSent(['from' => 'jane.doe@example.org']);
    TestEmail::assertSent(['subject' => 'Hello World!']);
});

it('fails if no email matching specified properties has been sent', function () {
    TestEmail::assertSent([
        'from' => 'john.doe@example.org',
        'subject' => 'Lorem ipsum',
    ]);
})->throws(
    exception: AssertionFailedError::class,
    exceptionMessage: 'Expected to have sent exactly one email, but sent none.',
);

it('fails if more than one email matching specified properties have been sent', function () {
    new TestEmail([
        'from' => 'jane.doe@example.org',
        'to' => 'john.doe@example.org',
        'subject' => 'Another emails',
        'body' => 'Body',
    ]);

    TestEmail::assertSent(['from' => 'jane.doe@example.org']);
})->throws(
    exception: AssertionFailedError::class,
    exceptionMessage: 'Expected to have sent exactly one email, but sent 2.',
);
