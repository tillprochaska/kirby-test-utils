<?php

namespace TillProchaska\KirbyTestUtils;

use Kirby\Cms\App as Kirby;
use Kirby\Http\Request;
use Kirby\Http\Response;
use Kirby\Http\Url;
use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * @internal
 * @coversNothing
 */
class TestCase extends BaseTestCase
{
    protected Kirby $kirby;
    protected array $defaultQuery = [];
    protected array $defaultParams = [];
    protected array $defaultHeaders = [];

    protected function setUp(): void
    {
        $this->kirby = $this->initializeKirbyInstance();

        $this->kirby->extend([
            'components' => [
                'email' => function ($kirby, $props, $debug) {
                    return new TestEmail($props);
                },
            ],
        ]);

        TestEmail::flushEmails();
    }

    public function request(string $method, string $path, array $query = [], array $params = [], array $headers = []): Response
    {
        $query = array_merge($this->defaultQuery, $query);
        $params = array_merge($this->defaultParams, $params);
        $headers = array_merge($this->defaultHeaders, $headers);

        // Kirby doesn’t provide a clean way to set the headers
        // of a `Request` instance or to replace the `Request`
        // class implementation, so we need to work around this
        // by temporarily setting the respective `$_SERVER` items.
        foreach ($this->normalizeHeaders($headers) as $name => $value) {
            $_SERVER[$name] = $value;
        }

        // We clone the Kirby instance and replace the current
        // request object and path. This way, Kirby’s internal
        // routing as well as any site-specific logic always
        // use the test request. The cloned Kirby instance will
        // also be returned whenever the Kirby singleton is
        // accessed (e.g. in the `kirby()` helper).
        $kirby = kirby();

        $requestProps = [
            'method' => $method,
            'url' => Url::build(['path' => $path, 'params' => $params]),
            'query' => $query,
        ];

        $tempKirby = kirby()->clone([
            'path' => $path,
            'request' => $requestProps,
            'components' => [
                'email' => kirby()->extensions('components')['email'],
            ],
        ]);

        // We then render the response for the test request using
        // the cloned Kirby instance.
        $response = $tempKirby->render();

        // ... and we also reset the delete the headers from `$_SERVER`
        foreach ($this->normalizeHeaders($headers) as $name => $value) {
            unset($_SERVER[$name]);
        }

        // Finally, we reset the singleton to the original instance.
        // There’s no public API to set the singleton. We clone the
        // original instance (without overwriting any props) as a
        // workaround.
        $kirby->clone();

        return new TestResponse(
            response: $response,
            request: new Request($requestProps),
        );
    }

    public function get(string $path, array $query = [], array $params = [], array $headers = []): Response
    {
        return $this->request('GET', $path, $query, $params, $headers);
    }

    public function post(string $path, array $query = [], array $params = [], array $headers = []): Response
    {
        return $this->request('POST', $path, $query, $params, $headers);
    }

    public function withQuery(array $query = []): self
    {
        $this->defaultQuery = array_merge($this->defaultQuery, $query);

        return $this;
    }

    public function withParams(array $params = []): self
    {
        $this->defaultParams = array_merge($this->defaultParams, $params);

        return $this;
    }

    public function withHeaders(array $headers = []): self
    {
        $this->defaultHeaders = array_merge($this->defaultHeaders, $headers);

        return $this;
    }

    public function kirby(): Kirby
    {
        return $this->kirby;
    }

    protected function initializeKirbyInstance(): Kirby
    {
        throw new \Exception('You need to override the `initKirby` method in your `TestCase`. See the README for additional instructions.');
    }

    protected function normalizeHeaders(array $headers = []): array
    {
        // https://datatracker.ietf.org/doc/html/rfc3875

        $normalized = [];

        foreach ($headers as $name => $value) {
            $name = 'HTTP_'.str_replace('-', '_', strtoupper($name));
            $normalized[$name] = $value;
        }

        return $normalized;
    }
}
