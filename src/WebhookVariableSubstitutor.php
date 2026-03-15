<?php

declare(strict_types=1);

namespace App;

/**
 * Substitutes {{variable}} placeholders in webhook response body and headers
 * using the incoming request (method, headers, body, query, IP).
 */
class WebhookVariableSubstitutor
{
    /**
     * Build request context for substitution.
     *
     * @param string $method HTTP method
     * @param string $headersJson JSON-encoded request headers (from getallheaders())
     * @param string $body Raw request body
     * @param string $queryString QUERY_STRING
     * @param string|null $ip Client IP
     * @return array{request: array{method: string, ip: string, body: string, body_parsed: array|null, headers: array<string, string>, query: array<string, string>}}
     */
    public static function buildContext(
        string $method,
        string $headersJson,
        string $body,
        string $queryString,
        ?string $ip
    ): array {
        // One value per header name (first occurrence wins, case-insensitive) for consistency and security
        $headersArray = [];
        if ($headersJson !== '') {
            $decoded = json_decode($headersJson, true);
            if (is_array($decoded)) {
                $seenLower = [];
                foreach ($decoded as $k => $v) {
                    if (is_string($k) && (is_string($v) || is_int($v))) {
                        $keyLower = strtolower($k);
                        if (!isset($seenLower[$keyLower])) {
                            $seenLower[$keyLower] = true;
                            $headersArray[$k] = (string) $v;
                        }
                    }
                }
            }
        }

        $bodyParsed = null;
        if ($body !== '') {
            $decoded = json_decode($body, true);
            if (is_array($decoded)) {
                $bodyParsed = $decoded;
            }
        }

        $queryArray = [];
        if ($queryString !== '') {
            parse_str($queryString, $queryArray);
            $queryArray = array_map(function ($v) {
                return is_scalar($v) ? (string) $v : '';
            }, $queryArray);
        }

        return [
            'request' => [
                'method' => $method,
                'ip' => $ip ?? '',
                'body' => $body,
                'body_parsed' => $bodyParsed,
                'headers' => $headersArray,
                'query' => $queryArray,
            ],
        ];
    }

    /**
     * Replace all {{...}} placeholders in template with values from context.
     * Supports: request.method, request.ip, request.body, request.body.key.path,
     * request.headers.Header-Name (case-insensitive), request.query.param.
     */
    public static function substitute(string $template, array $context): string
    {
        return preg_replace_callback(
            '/\{\{([^}]+)\}\}/',
            function (array $m) use ($context): string {
                $key = trim($m[1]);
                $value = self::resolve($key, $context);
                return $value !== null ? (string) $value : '';
            },
            $template
        );
    }

    /**
     * Resolve a single variable key from context.
     */
    private static function resolve(string $key, array $context): ?string
    {
        $request = $context['request'] ?? null;
        if (!is_array($request)) {
            return '';
        }

        if ($key === 'request.method') {
            return $request['method'] ?? '';
        }
        if ($key === 'request.ip') {
            return $request['ip'] ?? '';
        }
        if ($key === 'request.body') {
            return $request['body'] ?? '';
        }

        if (str_starts_with($key, 'request.body.') && strlen($key) > 12) {
            $path = substr($key, 12);
            $parsed = $request['body_parsed'] ?? null;
            if (!is_array($parsed)) {
                return '';
            }
            $v = self::getByDotPath($parsed, $path);
            return $v !== null ? (string) $v : '';
        }

        if (str_starts_with($key, 'request.headers.')) {
            $headerName = substr($key, 16);
            $headers = $request['headers'] ?? [];
            $value = self::getHeaderCaseInsensitive($headers, $headerName);
            return $value ?? '';
        }

        if (str_starts_with($key, 'request.query.') && strlen($key) > 13) {
            $param = substr($key, 13);
            $query = $request['query'] ?? [];
            $value = $query[$param] ?? null;
            return $value !== null ? (string) $value : '';
        }

        return '';
    }

    /**
     * Get nested array value by dot path (e.g. "user.email").
     */
    private static function getByDotPath(array $data, string $path): mixed
    {
        $segments = explode('.', $path);
        $current = $data;
        foreach ($segments as $seg) {
            if ($seg === '' || !is_array($current) || !array_key_exists($seg, $current)) {
                return null;
            }
            $current = $current[$seg];
        }
        return is_scalar($current) || $current === null ? $current : json_encode($current);
    }

    /**
     * Get header value with case-insensitive key match.
     */
    private static function getHeaderCaseInsensitive(array $headers, string $name): ?string
    {
        $nameLower = strtolower($name);
        foreach ($headers as $k => $v) {
            if (strtolower($k) === $nameLower) {
                return (string) $v;
            }
        }
        return null;
    }
}
