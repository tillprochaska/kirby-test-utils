<?php

use Kirby\Toolkit\Collection;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use TillProchaska\KirbyTestUtils\HasHtmlBody;

beforeEach(function () {
    $this->mock = $this->getMockForTrait(HasHtmlBody::class);
});

it('can select HTML elements using CSS selectors', function () {
    $this->mock->method('body')->willReturn('<a class="call-to-action">Read more</a>');
    $result = $this->mock->select('.call-to-action');

    expect($result)->toBeInstanceOf(Collection::class);
    expect($result->count())->toEqual(1);
    expect($result->first()->textContent)->toEqual('Read more');
});

it('can select HTML elements matching CSS selectors with given text', function () {
    $this->mock->method('body')->willReturn('<a href="/tos">Terms</a><a href="/sign-up">Sign up</a>');
    $result = $this->mock->select('a', text: 'Sign up');

    expect($result)->toHaveCount(1);
    expect($result->first()->getAttribute('href'))->toEqual('/sign-up');
});

it('can select HTML elements matching CSS selectors with given attributes', function () {
    $this->mock->method('body')->willReturn('
        <meta name="description" content="Default description" />
        <meta name="og:description" content="Open Graph description" />
    ');

    $result = $this->mock->select('meta', attributes: ['content' => 'Open Graph description']);
    expect($result)->toHaveCount(1);
    expect($result->first()->getAttribute('name'))->toEqual('og:description');

    $result = $this->mock->select('meta', attributes: [
        'name' => 'description',
        'content' => 'Open Graph description',
    ]);

    expect($result)->toHaveCount(0);
});

it('passes if response body contains string', function () {
    $this->mock->method('body')->willReturn('Lorem ipsum');
    expect($this->mock->assertSee('ipsum', 'Lorem'))->toEqual($this->mock);
});

it('fails if response body does not contain string', function () {
    $this->mock->method('body')->willReturn('Lorem ipsum');
    $this->mock->assertSee('Not a substring of the response body.');
})->throws(AssertionFailedError::class);

it('passes if response body contains text', function () {
    $this->mock->method('body')->willReturn('<h1>Lorem ipsum</h1>');
    expect($this->mock->assertSeeText('Lorem ipsum'))->toEqual($this->mock);
});

it('fails if response body does not contain text', function () {
    $this->mock->method('body')->willReturn('<h1>Lorem ipsum</h1>');
    expect($this->mock->assertSeeText('Lorem ipsum', '<h1>'));
})->throws(AssertionFailedError::class);

it('passes if response contains selector', function () {
    $this->mock->method('body')->willReturn('<h1>Lorem ipsum</h1>');
    expect($this->mock->assertSelector('h1'))->toEqual($this->mock);
});

it('increments the assertion count if response contains selector', function () {
    $this->mock->method('body')->willReturn('<h1>Lorem ipsum</h1>');

    $this->mock->assertSelector('h1');
    expect(Assert::getCount())->toEqual(1);

    $this->mock->assertSelector('h1', count: 1);
    expect(Assert::getCount())->toEqual(3);

    $this->mock->assertSelector('h1', text: 'Lorem ipsum');
    expect(Assert::getCount())->toEqual(5);
});

it('fails if response does not contain selector', function () {
    $this->mock->method('body')->willReturn('<h1>Lorem ipsum</h1>');
    $this->mock->assertSelector('h2');
})->throws(
    exception: AssertionFailedError::class,
    exceptionMessage: 'has at least one matching element.',
);

it('passes if response contains exact number of elements matching selector', function () {
    $body = '<ul><li>One</li><li>Two</li><li>Three</li></ul>';
    $this->mock->method('body')->willReturn($body);

    expect($this->mock->assertSelector('li', count: 3))->toEqual($this->mock);
});

it('fails if response does not contain exact number of elements matching selector', function () {
    $body = '<ul><li>One</li><li>Two</li><li>Three</li></ul>';
    $this->mock->method('body')->willReturn($body);

    $this->mock->assertSelector('li', count: 2);
})->throws(
    exception: AssertionFailedError::class,
    exceptionMessage: 'has exactly 2 matching elements.',
);

it('passes if response contains selector with text', function () {
    $this->mock->method('body')->willReturn('<h1>Lorem ipsum</h1>');
    expect($this->mock->assertSelector('h1', text: 'Lorem ipsum'))->toEqual($this->mock);
});

it('fails if response does not contains selector with text', function () {
    $this->mock->method('body')->willReturn('<h1>Lorem ipsum</h1>');
    $this->mock->assertSelector('h1', text: 'Hello World!');
})->throws(
    exception: AssertionFailedError::class,
    exceptionMessage: 'has at least one matching element.',
);

it('passes if response contains exact number of elements matching selector with text', function () {
    $body = '<ul><li>One</li><li>One</li><li>One</li></ul>';
    $this->mock->method('body')->willReturn($body);

    expect($this->mock->assertSelector('li', count: 3, text: 'One'))->toEqual($this->mock);
});

it('fails if response does not contain exact number of elements matching selector and text', function () {
    $body = '<ul><li>One</li><li>Two</li><li>Three</li></ul>';
    $this->mock->method('body')->willReturn($body);

    $this->mock->assertSelector('li', count: 3, text: 'One');
})->throws(
    exception: AssertionFailedError::class,
    exceptionMessage: 'has exactly 3 matching elements.',
);
