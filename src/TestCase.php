<?php

namespace TillProchaska\KirbyTestUtils;

use Kirby\Cms\App as Kirby;
use Kirby\Http\Request;
use Kirby\Http\Response;
use Kirby\Http\Uri;
use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * @internal
 * @coversNothing
 */
class TestCase extends BaseTestCase
{
    protected ?Kirby $kirby;
    protected array $options = [];
    protected array $defaultQuery = [];
    protected array $defaultParams = [];
    protected array $defaultHeaders = [];

    protected function setUp(): void
    {
        $this->kirby = null;
        TestEmail::flushEmails();
    }

    public function request(string $method, string $url, array $query = [], array $params = [], array $headers = []): Response
    {
        $url = new Uri($url);
        $query = array_merge($this->defaultQuery, $url->query()->toArray(), $query);
        $params = array_merge($this->defaultParams, $url->params()->toArray(), $params);

        $url->setQuery($query);
        $url->setParams($params);

        $headers = array_merge($this->defaultHeaders, $headers);

        // Fake $_SERVER variables
        $server = [
            'HTTPS' => 'https' === $url->scheme() ? true : false,
            'SERVER_NAME' => $url->host(),
            'SERVER_PORT' => $url->port(),
            'REQUEST_METHOD' => strtoupper($method),
            'REQUEST_URI' => (new Uri([
                'path' => $url->path()->toString(),
                'query' => $url->query(),
            ]))->toString(),
            'PATH_INFO' => '/'.$url->path()->toString(),
            'QUERY_STRING' => $url->query()->toString(),
        ];

        foreach ($server as $key => $value) {
            $_SERVER[$key] = $value;
        }

        // Kirby doesn’t provide a clean way to set the headers
        // of a `Request` instance or to replace the `Request`
        // class implementation, so we need to work around this
        // by temporarily setting the respective `$_SERVER` items.
        foreach ($this->normalizeHeaders($headers) as $name => $value) {
            $_SERVER[$name] = $value;
        }

        // We create a new temporary Kirby instance and explicitly pass
        // the request properties. This way, Kirby’s internal routing as
        // well as any site-specific logic always use the test request.
        // The temporary Kirby instance will also be returned whenever the
        // Kirby singleton is accessed (e.g. in the `kirby()` helper).

        // Keep a reference to the original Kirby instance, so we can reset
        // it after we’ve made the request.
        $originalKirby = $this->kirby();

        $requestProps = [
            'method' => $method,
            'url' => $url->toString(),
            'query' => $query,
        ];

        // There’s no public API to set the singleton, so the easiest
        // workaround is to clone it.
        $tempKirby = $this->initKirby([
            'path' => $url->path()->toString(),
            'request' => $requestProps,
        ])->clone(setInstance: true);

        // Handle authenticated users
        if ($user = $originalKirby->user()) {
            $tempKirby->impersonate($user->id());
        }

        // Render the response for the test request using the temporary Kirby instance
        $response = $tempKirby->render();

        // Reset HTTP headers
        foreach ($this->normalizeHeaders($headers) as $name => $value) {
            unset($_SERVER[$name]);
        }

        // Reset default $_SERVER variables
        foreach ($server as $key => $value) {
            unset($_SERVER[$key]);
        }

        // Finally, we reset the singleton to the original instance.
        $originalKirby->clone(setInstance: true);

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

    public function withOption(string $key, mixed $value): self
    {
        $this->options[$key] = $value;

        return $this;
    }

    public function kirby(): Kirby
    {
        if (null === $this->kirby) {
            $this->beforeKirbyInit();

            // There is no public API to set the instance singleton, so
            // the easiest workaround is to clone it
            $this->kirby = $this->initKirby()->clone(setInstance: true);

            $this->afterKirbyInit();
        }

        return $this->kirby;
    }

    protected function beforeKirbyInit(): void
    {
    }

    protected function kirbyProps(): array
    {
        return [];
    }

    protected function initKirby(array $props = []): Kirby
    {
        return new Kirby(array_merge_recursive(
            $this->kirbyProps(),
            $props,

            // Setup test email component
            [
                'components' => [
                    'email' => function ($kirby, $props, $debug) {
                        return new TestEmail($props);
                    },
                ],
            ],

            // Set options
            [
                'options' => $this->options,
            ]
        ), setInstance: false);
    }

    protected function afterKirbyInit(): void
    {
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
