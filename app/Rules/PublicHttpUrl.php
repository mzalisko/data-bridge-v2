<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates that a URL points to a public HTTP(S) host — not a loopback,
 * private (RFC1918/RFC4193), link-local, or reserved IP range.
 *
 * Defense against SSRF: the attacker who can set push_url could otherwise
 * make the CRM POST signed HMAC requests to internal services
 * (127.0.0.1:6379, 169.254.169.254/cloud metadata, etc.).
 *
 * Skips the private-IP check in local/testing environments so that
 * docker-internal hostnames (host.docker.internal, wp container) work
 * for development.
 */
class PublicHttpUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $parsed = parse_url($value);
        $scheme = strtolower($parsed['scheme'] ?? '');
        if (!in_array($scheme, ['http', 'https'], true)) {
            $fail('Тільки http/https URL дозволено');
            return;
        }

        $host = strtolower($parsed['host'] ?? '');
        if ($host === '') {
            $fail('URL не має хоста');
            return;
        }

        if (app()->environment(['local', 'testing'])) {
            return;
        }

        if ($this->isPrivateHost($host)) {
            $fail('URL не може вказувати на приватну або loopback-мережу');
        }
    }

    private function isPrivateHost(string $host): bool
    {
        $nameBlocklist = ['localhost', 'broadcasthost', 'ip6-localhost', 'ip6-loopback'];
        if (in_array($host, $nameBlocklist, true)) {
            return true;
        }
        if (str_ends_with($host, '.local') || str_ends_with($host, '.internal')) {
            return true;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return !filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
        }

        $records = @dns_get_record($host, DNS_A | DNS_AAAA);
        if ($records === false || $records === []) {
            return true;
        }
        foreach ($records as $r) {
            $ip = $r['ip'] ?? $r['ipv6'] ?? null;
            if ($ip && !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return true;
            }
        }
        return false;
    }
}
