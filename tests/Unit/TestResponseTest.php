<?php

use Kirby\Http\Response;
use Kirby\Http\Uri;
use PHPUnit\Framework\AssertionFailedError;
use TillProchaska\KirbyTestUtils\TestResponse;

it('can be constructed', function () {
    $response = new TestResponse(['body' => 'Lorem ipsum']);
    expect($response)->toBeInstanceOf(TestResponse::class);
    expect($response->body())->toEqual('Lorem ipsum');
});

it('can be constructed with an instance of `Kirby\Cms\Response`', function () {
    $kirbyResponse = new Response(
        body: 'Moved to https://example.org.',
        type: 'text/plain',
        code: '301',
        headers: ['Location' => 'https://example.org'],
        charset: 'ascii',
    );

    $response = new TestResponse($kirbyResponse);

    expect($response->body())->toEqual('Moved to https://example.org.');
    expect($response->type())->toEqual('text/plain');
    expect($response->code())->toEqual(301);
    expect($response->headers())->toEqual(['Location' => 'https://example.org']);
    expect($response->charset())->toEqual('ascii');
});

it('passes if response has expected status code', function () {
    $response = new TestResponse(['code' => 404]);
    expect($response->assertCode(404))->toEqual($response);
});

it('fails if response does not have expected status code', function () {
    $response = new TestResponse(['code' => 404]);
    $response->assertCode(200);
})->throws(AssertionFailedError::class);

it('passes if response header has expected value', function () {
    $response = new TestResponse([
        'headers' => [
            'Content-Type' => 'application/json',
        ],
    ]);

    expect($response->assertHeader('content-type', 'application/json'))->toEqual($response);
});

it('passes if response header has non-string value', function () {
    $response = new TestResponse([
        'headers' => [
            'Location' => new Uri('https://example.org'),
        ],
    ]);

    expect($response->assertHeader('Location', 'https://example.org'))->toEqual($response);
});

it('fails if response header does not have expected value', function () {
    $response = new TestResponse([
        'headers' => [
            'Content-Type' => 'application/json',
        ],
    ]);

    $response->assertHeader('content-type', 'text/plain');
})->throws(AssertionFailedError::class);
