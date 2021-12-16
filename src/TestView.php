<?php

namespace TillProchaska\KirbyTestUtils;

class TestView
{
    use HasHtmlBody;

    public function __construct(protected string $body)
    {
    }

    public function body(): string
    {
        return $this->body;
    }
}
