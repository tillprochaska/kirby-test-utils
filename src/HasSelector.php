<?php

namespace TillProchaska\KirbyTestUtils;

use PHPUnit\Framework\Constraint\Constraint;

class HasSelector extends Constraint
{
    public function __construct(
        private string $selector,
        private ?int $count = null,
        private ?string $text = null,
        private ?array $attributes = null,
    ) {
    }

    public function toString(): string
    {
        if (null !== $this->count) {
            return "has exactly {$this->count} matching elements";
        }

        return 'has at least one matching element';
    }

    protected function matches(mixed $other): bool
    {
        $elements = $other->select($this->selector, $this->text, $this->attributes);
        $actualCount = $elements->count();

        if (null !== $this->count) {
            if ($this->count !== $actualCount) {
                return false;
            }

            return true;
        }

        if ($actualCount <= 0) {
            return false;
        }

        return true;
    }
}
