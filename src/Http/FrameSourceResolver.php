<?php

namespace Emaia\LaravelHotwireTurbo\Http;

use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use ValueError;

final class FrameSourceResolver
{
    public function __construct(private Session $session) {}

    /** Resolve an explicit, redirect-safe source URL for the current Turbo Frame request. */
    public function resolve(Request $request): ?string
    {
        if ($this->frameId($request) === null) {
            return null;
        }

        return $this->resolveCandidates($request, [
            ['source' => 'input', 'value' => $request->input('_turbo_frame_src')],
            ['source' => 'header', 'value' => $request->header('X-Turbo-Frame-Src')],
        ]);
    }

    /**
     * Resolve the validation redirect while retaining the session fallback for compatibility.
     *
     * @internal
     */
    public function resolveValidationRedirect(Request $request): string
    {
        $source = $this->resolveCandidates($request, [
            ['source' => 'input', 'value' => $request->input('_turbo_frame_src')],
            ['source' => 'header', 'value' => $request->header('X-Turbo-Frame-Src')],
        ]);

        if ($source !== null) {
            return $source;
        }

        $session = $request->hasSession() ? $request->session() : $this->session;
        $source = $this->resolveCandidates($request, [
            ['source' => 'session', 'value' => $session->get('_previous.url')],
        ]);

        if ($source !== null) {
            return $source;
        }

        throw new RuntimeException(
            'TurboFormRequest: unable to determine a safe frame source URL. '.
            'Add @turboFrameSrc to the form rendered inside the Turbo Frame.'
        );
    }

    /**
     * @param  list<array{source: string, value: mixed}>  $candidates
     */
    private function resolveCandidates(Request $request, array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            $value = $candidate['value'];

            if ($value === null || $value === '') {
                continue;
            }

            $reason = $this->rejectionReason($request, $value);

            if ($reason === null && is_string($value)) {
                return $value;
            }

            Log::warning('Rejected Turbo Frame source URL.', [
                'source' => $candidate['source'],
                'reason' => $reason,
                'type' => get_debug_type($value),
                'length' => is_string($value) ? strlen($value) : null,
            ]);
        }

        return null;
    }

    private function rejectionReason(Request $request, mixed $candidate): ?string
    {
        if (! is_string($candidate)) {
            return 'non_string';
        }

        if ($candidate === '' || preg_match('/[\x00-\x20\x7F\\\\]/', $candidate) === 1) {
            return 'forbidden_character';
        }

        if (preg_match('/%(?![0-9A-Fa-f]{2})/', $candidate) === 1) {
            return 'malformed_percent_encoding';
        }

        $parts = $this->parseUrl($candidate);

        if ($parts === null) {
            return 'malformed_url';
        }

        if (str_starts_with($candidate, '/')) {
            if (str_starts_with($candidate, '//')) {
                return 'protocol_relative';
            }

            return isset($parts['scheme'], $parts['host']) ? 'malformed_relative_url' : null;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));

        if (! in_array($scheme, ['http', 'https'], true)) {
            return 'unsupported_scheme';
        }

        if ($host === '') {
            return 'missing_host';
        }

        if (isset($parts['user']) || isset($parts['pass'])) {
            return 'userinfo';
        }

        $authorityReason = $this->authorityRejectionReason($candidate, $parts);

        if ($authorityReason !== null) {
            return $authorityReason;
        }

        return in_array($host, $this->trustedHosts($request), true)
            ? null
            : 'untrusted_host';
    }

    /** @param array<string, int|string> $parts */
    private function authorityRejectionReason(string $url, array $parts): ?string
    {
        $separator = strpos($url, '://');

        if ($separator === false) {
            return 'malformed_authority';
        }

        $start = $separator + 3;
        $authority = substr($url, $start, strcspn($url, '/?#', $start));
        $host = (string) ($parts['host'] ?? '');
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';

        if ($authority === '' || str_contains($authority, '%')) {
            return 'malformed_authority';
        }

        if (strcasecmp($authority, $host.$port) !== 0) {
            return 'malformed_authority';
        }

        if (str_starts_with($host, '[') || str_ends_with($host, ']')) {
            if (! str_starts_with($host, '[') || ! str_ends_with($host, ']')) {
                return 'malformed_host';
            }

            return filter_var(substr($host, 1, -1), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false
                ? 'malformed_host'
                : null;
        }

        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
            return null;
        }

        $hostWithoutTrailingDot = rtrim($host, '.');
        $lastLabel = strrchr($hostWithoutTrailingDot, '.');
        $lastLabel = $lastLabel === false ? $hostWithoutTrailingDot : substr($lastLabel, 1);

        if (preg_match('/\A(?:0x[0-9a-f]+|[0-9]+)\z/i', $lastLabel) === 1) {
            return 'ambiguous_ipv4';
        }

        return filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false
            ? 'malformed_host'
            : null;
    }

    /** @return array<string, int|string>|null */
    private function parseUrl(string $url): ?array
    {
        try {
            $parts = parse_url($url);
        } catch (ValueError) {
            return null;
        }

        return is_array($parts) ? $parts : null;
    }

    /** @return list<string> */
    private function trustedHosts(Request $request): array
    {
        $hosts = [
            $request->getHost(),
            $this->hostFromConfig((string) config('app.url')),
        ];

        foreach ((array) config('turbo.trusted_redirect_hosts', []) as $entry) {
            if (is_string($entry)) {
                $hosts[] = $this->hostFromConfig($entry);
            }
        }

        return array_values(array_unique(array_filter(array_map(
            fn (?string $host): ?string => $host === null ? null : strtolower($host),
            $hosts,
        ))));
    }

    private function hostFromConfig(string $entry): ?string
    {
        if ($entry === '') {
            return null;
        }

        try {
            $host = parse_url($entry, PHP_URL_HOST);
        } catch (ValueError) {
            return null;
        }

        if (is_string($host) && $host !== '') {
            return $host;
        }

        return str_contains($entry, '://') ? null : $entry;
    }

    private function frameId(Request $request): ?string
    {
        $frame = $request->header('Turbo-Frame');

        if (! is_string($frame) || trim($frame) === '') {
            return null;
        }

        return trim($frame);
    }
}
