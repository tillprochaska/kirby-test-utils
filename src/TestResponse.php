<?php

namespace TillProchaska\KirbyTestUtils;

use Kirby\Http\Request;
use Kirby\Http\Response;
use PHPUnit\Framework\Assert;

class TestResponse extends Response
{
    use HasHtmlBody;

    protected ?Request $request;

    public function __construct(array|Response $response, ?Request $request = null)
    {
        if (is_array($response)) {
            return parent::__construct($response);
        }

        $this->request = $request;

        return parent::__construct(
            body: $response->body(),
            type: $response->type(),
            code: $response->code(),
            headers: $response->headers(),
            charset: $response->charset(),
        );
    }

    public function request(): ?Request
    {
        return $this->request;
    }

    public function assertCode(int $code): self
    {
        Assert::assertSame($code, $this->code());

        return $this;
    }

    public function assertHeader(string $name, string $value): self
    {
        $headers = array_change_key_case($this->headers());
        $name = strtolower($name);
        $actualValue = $headers[$name] ?? null;

        if (null !== $actualValue) {
            $actualValue = (string) $actualValue;
        }

        Assert::assertSame($value, $actualValue);

        return $this;
    }
}
