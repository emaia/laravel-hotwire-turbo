<?php

namespace Emaia\LaravelHotwireTurbo\Testing;

use Closure;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Assert;

class AssertableTurboStream
{
    /** @var Collection<int, \DOMElement> */
    public Collection $turboStreams;

    public function __construct(Collection $turboStreams)
    {
        $this->turboStreams = $turboStreams;
    }

    public function has(int $expectedCount): static
    {
        Assert::assertCount($expectedCount, $this->turboStreams);

        return $this;
    }

    public function hasTurboStream(?Closure $callback = null): static
    {
        $callback ??= fn ($matcher) => $matcher;
        $attrs = collect();

        $matches = $this->turboStreams
            ->mapInto(TurboStreamMatcher::class)
            ->filter(function (TurboStreamMatcher $matcher) use ($callback, $attrs): bool {
                $matcher = $callback($matcher);

                if (! $matcher->matches()) {
                    $attrs->add($matcher->attrs());

                    return false;
                }

                return true;
            });

        Assert::assertTrue(
            $matches->count() >= 1,
            sprintf(
                'Expected to find a matching Turbo Stream for `%s`, but none was found.',
                $attrs->unique()->join(' '),
            )
        );

        return $this;
    }
}
