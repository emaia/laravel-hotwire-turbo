<?php

namespace Emaia\LaravelHotwireTurbo\Testing;

use DOMDocument;
use DOMElement;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;

class ConvertTestResponseToTurboStreamCollection
{
    /**
     * @return Collection<int, DOMElement>
     */
    public static function convert(TestResponse $response): Collection
    {
        $dom = new DOMDocument;

        libxml_use_internal_errors(true);
        $dom->loadHTML($response->getContent(), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $elements = $dom->getElementsByTagName('turbo-stream');
        $streams = [];

        foreach ($elements as $element) {
            $streams[] = $element;
        }

        return collect($streams);
    }
}
