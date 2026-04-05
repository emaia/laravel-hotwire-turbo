<?php

namespace Emaia\LaravelHotwireTurbo\Testing;

use Closure;
use DOMElement;
use PHPUnit\Framework\Assert;

class TurboStreamMatcher
{
    /** @var array<string, string> */
    private array $wheres = [];

    /** @var array<int, string> */
    private array $contents = [];

    public function __construct(private DOMElement $turboStream) {}

    public function where(string $prop, string $value): static
    {
        $matcher = clone $this;
        $matcher->wheres[$prop] = $value;

        return $matcher;
    }

    public function see(string $content): static
    {
        $matcher = clone $this;
        $matcher->contents[] = $content;

        return $matcher;
    }

    public function matches(?Closure $callback = null): bool
    {
        if ($callback instanceof Closure) {
            return $callback($this)->matches();
        }

        if (! $this->matchesProps()) {
            return false;
        }

        return $this->matchesContents();
    }

    public function attrs(): string
    {
        $parts = [];
        foreach ($this->wheres as $key => $value) {
            $parts[] = "{$key}=\"{$value}\"";
        }

        return implode(' ', $parts);
    }

    private function matchesProps(): bool
    {
        foreach ($this->wheres as $prop => $value) {
            $propValue = $this->turboStream->getAttribute($prop);

            if ($propValue === '' || $propValue !== $value) {
                return false;
            }
        }

        return true;
    }

    private function matchesContents(): bool
    {
        if ($this->contents === []) {
            return true;
        }

        $html = $this->renderElement();

        foreach ($this->contents as $content) {
            Assert::assertStringContainsString($content, $html);
        }

        return true;
    }

    private function renderElement(): string
    {
        $html = '';
        $children = $this->turboStream->childNodes;

        foreach ($children as $child) {
            $html .= $child->ownerDocument->saveXML($child);
        }

        return $html;
    }
}
