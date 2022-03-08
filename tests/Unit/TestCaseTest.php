<?php

use Kirby\Http\Request;
use TillProchaska\KirbyTestUtils\TestEmail;
use TillProchaska\KirbyTestUtils\TestResponse;
use TillProchaska\KirbyTestUtils\Tests\TestTestCase;

beforeEach(function () {
    $this->testCase = new TestTestCase();
    $this->testCase->setUp();
});

it('makes HTTP requests', function () {
    $response = $this->testCase->request('GET', '/request-method');
    expect($response)->toEqual($response);
    expect($response->body())->toEqual('GET');

    $response = $this->testCase->request('POST', '/request-method');
    expect($response->body())->toEqual('POST');
});

it('returns a `TestResponse`', function () {
    expect($this->testCase->get('/hello-world'))->toBeInstanceOf(TestResponse::class);
});

it('exposes the original request on the response object', function () {
    $request = $this->testCase->get('/hello-world')->request();
    expect($request)->toBeInstanceOf(Request::class);
    expect($request->url())->toEqual('/hello-world');
});

it('has convenience method for GET requests', function () {
    $body = $this->testCase->get('/request-method')->body();
    expect($body)->toEqual('GET');
});

it('has convenience method for POST requests', function () {
    $body = $this->testCase->post('/request-method')->body();
    expect($body)->toEqual('POST');
});

it('accepts a query parameters array', function () {
    $body = $this->testCase->get('/query', query: ['foo' => 'bar'])->body();
    expect($body)->toEqual('{"foo":"bar"}');
});

it('accepts a parameters array', function () {
    $response = $this->testCase->get('/params', params: ['foo' => 'bar']);
    expect($response->body())->toEqual('{"foo":"bar"}');
    expect($response->request()->url())->toEqual('/params/foo:bar');
});

it('accepts an headers array', function () {
    $body = $this->testCase->get('/headers', headers: ['foo' => 'bar'])->body();
    expect($body)->toEqual('{"Foo":"bar"}');
});

it('has a convenience method to set default query', function () {
    $body = $this->testCase
        ->withQuery(['foo' => 'bar', 'lorem' => 'ipsum'])
        ->get('/query', query: ['foo' => 'baz', 'hello' => 'world'])
        ->body()
    ;

    expect($body)->toEqual('{"foo":"baz","lorem":"ipsum","hello":"world"}');
});

it('has a convenience method to set default params', function () {
    $response = $this->testCase
        ->withParams(['foo' => 'bar', 'lorem' => 'ipsum'])
        ->get('/params', params: ['foo' => 'baz', 'hello' => 'world'])
    ;

    expect($response->body())->toEqual('{"foo":"baz","lorem":"ipsum","hello":"world"}');
    expect($response->request()->url())->toEqual('/params/foo:baz/lorem:ipsum/hello:world');
});

it('has a convenience method to set default headers', function () {
    $body = $this->testCase
        ->withHeaders(['foo' => 'bar', 'lorem' => 'ipsum'])
        ->get('/headers', headers: ['foo' => 'baz', 'hello' => 'world'])
        ->body()
    ;

    expect($body)->toEqual('{"Foo":"baz","Lorem":"ipsum","Hello":"world"}');
});

it('does not change request headers for subsequent test cases', function () {
    $testCase = new TestTestCase();
    $testCase->setUp();
    $body = $testCase->withHeaders(['foo' => 'bar'])->get('/headers')->body();
    expect($body)->toEqual('{"Foo":"bar"}');

    $testCase = new TestTestCase();
    $testCase->setUp();
    $body = $testCase->get('/headers')->body();
    expect($body)->toEqual('{}');
});

it('handles custom HTTP headers prefixed with X', function () {
    $testCase = new TestTestCase();
    $testCase->setUp();
    $body = $testCase->withHeaders(['x-foo' => 'bar'])->get('/headers')->body();
    expect($body)->toEqual('{"X-Foo":"bar"}');

    $testCase = new TestTestCase();
    $testCase->setUp();
    $body = $testCase->get('/headers')->body();
    expect($body)->toEqual('{}');
});

it('replaces Kirby’s default email component with `TestEmail` class', function () {
    $email = $this->testCase->kirby()->email([
        'from' => 'jane.doe@example.org',
        'to' => 'john.doe@example.org',
        'subject' => 'Hello World',
        'body' => 'Body',
    ]);

    expect($email)->toBeInstanceOf(TestEmail::class);
});

it('replaces Kirby’s default email component when testing HTTP requests', function () {
    // See `support/config/config.php` for the route that sends the test email
    $this->testCase->get('/email');

    TestEmail::assertSent([
        'from' => 'john.doe@example.org',
    ]);
});

it('flushes test emails before every test case', function () {
    $props = [
        'from' => 'jane.doe@example.org',
        'to' => 'john.doe@example.org',
        'subject' => 'Hello World!',
        'body' => 'Body',
    ];

    $this->testCase->kirby()->email($props);
    expect(TestEmail::emails())->toHaveCount(1);

    $this->testCase->setUp();

    $this->testCase->kirby()->email($props);
    expect(TestEmail::emails())->toHaveCount(1);
});

it('sets Kirby options', function () {
    $value = $this->testCase
        ->withOption('my-option', 'Hello World!')
        ->kirby()->option('my-option');

    expect($value)->toEqual('Hello World!');
});
