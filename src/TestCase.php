<?php

namespace TillProchaska\KirbyTestUtils;

use Kirby\Cms\App as Kirby;
use Kirby\Http\Request;
use Kirby\Http\Response;
use Kirby\Http\Uri;
use Kirby\Http\Url;
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
    }

    public function request(string $method, string $url, array $query = [], array $params = [], array $headers = []): Response
    {
        $url = new Uri($url);
        $query = array_merge($this->defaultQuery, $url->query()->toArray(), $query);
        $params = array_merge($this->defaultParams, $url->params()->toArray(), $params);

        $url->setQuery($query);
        $url->setParams($params);

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
        $kirby = $this->kirby();

        $requestProps = [
            'method' => $method,
            'url' => $url->toString(),
            'query' => $query,
        ];

        $tempKirby = kirby()->clone([
            'path' => $url->path()->toString(),
            'request' => $requestProps,
            'components' => [
                'email' => kirby()->extensions('components')['email'],
            ],
        ]);

        if ($user = $kirby->user()) {
            $tempKirby->impersonate($user->id());
        }

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

    public function withOption(string $key, mixed $value): self
    {
        $this->options[$key] = $value;

        return $this;
    }

    public function kirby(): Kirby
    {
        if (null === $this->kirby) {
            $this->beforeKirbyInit();
            $this->initKirby();
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

    protected function initKirby(): void
    {
        $this->kirby = new Kirby(array_merge_recursive(
            $this->kirbyProps(),

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
        ));

        TestEmail::flushEmails();
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
