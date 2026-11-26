<?php

namespace Emaia\LaravelHotwireTurbo\Http;

use Illuminate\Http\Request;

final class TurboRequestVariation
{
    private const ATTRIBUTE = self::class;

    private bool $marked = false;

    private function __construct() {}

    /** Start tracking before Laravel copies the request into a FormRequest. */
    public static function track(Request $request): void
    {
        self::state($request);
    }

    /** Mark that the response can depend on Turbo request headers, including their absence. */
    public static function mark(Request $request): void
    {
        self::state($request)->marked = true;
    }

    /** Check whether the request or a tracked copy consulted Turbo negotiation. */
    public static function isMarked(Request $request): bool
    {
        $state = $request->attributes->get(self::ATTRIBUTE);

        return $state instanceof self && $state->marked;
    }

    private static function state(Request $request): self
    {
        $state = $request->attributes->get(self::ATTRIBUTE);

        if (! $state instanceof self) {
            $state = new self;
            $request->attributes->set(self::ATTRIBUTE, $state);
        }

        return $state;
    }
}
