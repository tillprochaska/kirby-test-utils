<?php

namespace TillProchaska\KirbyTestUtils;

use DOMDocument;
use DOMXPath;
use Kirby\Toolkit\Collection;
use PHPUnit\Framework\Assert;
use Symfony\Component\CssSelector\CssSelectorConverter;

trait HasHtmlBody
{
    abstract public function body(): string;

    public function select(string $selector, ?string $text = null, ?array $attributes = null): Collection
    {
        libxml_use_internal_errors(true);

        $document = new DOMDocument();
        $document->loadHTML($this->body());

        $converter = new CssSelectorConverter();
        $xPathSelector = $converter->toXPath($selector);

        $xPath = new DOMXPath($document);
        $elements = $xPath->query($xPathSelector);

        $elements = new Collection([...$elements]);

        if (null !== $text) {
            $elements = $elements->filter(fn ($element) => trim($element->textContent) === trim($text));
        }

        if (null !== $attributes) {
            $elements = $elements->filter(function ($element) use ($attributes) {
                foreach ($attributes as $name => $value) {
                    if ($element->getAttribute($name) !== $value) {
                        return false;
                    }
                }

                return true;
            });
        }

        return $elements;
    }

    public function assertSee(string ...$strings): self
    {
        foreach ($strings as $string) {
            Assert::assertStringContainsString($string, $this->body());
        }

        return $this;
    }

    public function assertSeeText(string ...$strings): self
    {
        foreach ($strings as $string) {
            Assert::assertStringContainsString($string, strip_tags($this->body()));
        }

        return $this;
    }

    public function assertSelector(string $selector, ?int $count = null, ?string $text = null, ?array $attributes = null): self
    {
        $constraint = new HasSelector($selector, $count, $text, $attributes);

        Assert::assertThat($this, $constraint);

        return $this;
    }
}
