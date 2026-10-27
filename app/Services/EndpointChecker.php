<?php

namespace App\Services;

use App\Models\CheckResult;
use App\Models\MonitoredEndpoint;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class EndpointChecker
{
    public function check(MonitoredEndpoint $endpoint): CheckResult
    {
        $startedAt = hrtime(true);
        $httpStatus = null;
        $errorMessage = null;
        $status = 'down';

        try {
            [$host, $ip, $port] = $this->validateAndResolve(
                $endpoint->url
            );

            $response = Http::timeout(10)
                ->connectTimeout(5)
                ->withOptions([
                    'allow_redirects' => false,
                    'proxy' => '',
                    'curl' => [
                        CURLOPT_RESOLVE => [
                            "{$host}:{$port}:{$ip}",
                        ],
                        CURLOPT_NOPROGRESS => false,
                        CURLOPT_XFERINFOFUNCTION =>
                            function (
                                $resource,
                                $downloadSize,
                                $downloaded,
                                $uploadSize,
                                $uploaded
                            ) {
                                return $downloaded > 1048576 ? 1 : 0;
                            },
                    ],
                ])
                ->get($endpoint->url);

            $httpStatus = $response->status();

            if ($httpStatus >= 200 && $httpStatus < 400) {
                $status = 'up';
            } else {
                $errorMessage = "HTTP status {$httpStatus}";
            }
        } catch (Throwable $e) {
            $errorMessage = mb_substr($e->getMessage(), 0, 1000);
        }

        $responseTime = (int) round(
            (hrtime(true) - $startedAt) / 1_000_000
        );

        return CheckResult::create([
            'endpoint_id' => $endpoint->id,
            'status' => $status,
            'http_status_code' => $httpStatus,
            'response_time_ms' => $responseTime,
            'error_message' => $errorMessage,
            'checked_at' => now(),
        ]);
    }

    private function validateAndResolve(string $url): array
    {
        $parts = parse_url($url);

        if ($parts === false) {
            throw new RuntimeException('URL tidak valid.');
        }

        $scheme = strtolower($parts['scheme'] ?? '');
        $host = strtolower(rtrim($parts['host'] ?? '', '.'));

        if (
            !in_array($scheme, ['http', 'https'], true) ||
            $host === '' ||
            isset($parts['user']) ||
            isset($parts['pass'])
        ) {
            throw new RuntimeException('URL tidak diizinkan.');
        }

        $port = $parts['port'] ?? (
            $scheme === 'https' ? 443 : 80
        );

        if (
            ($scheme === 'http' && $port !== 80) ||
            ($scheme === 'https' && $port !== 443)
        ) {
            throw new RuntimeException(
                'Hanya port HTTP 80 dan HTTPS 443 yang diizinkan.'
            );
        }

        if (
            filter_var($host, FILTER_VALIDATE_IP) !== false ||
            !preg_match(
                '/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)(?:\.(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?))*$/',
                $host
            )
        ) {
            throw new RuntimeException(
                'Gunakan hostname publik yang valid, bukan IP langsung.'
            );
        }

        $records = dns_get_record($host, DNS_A | DNS_AAAA);

        if ($records === false || count($records) === 0) {
            throw new RuntimeException(
                'Hostname tidak memiliki DNS record yang valid.'
            );
        }

        $ipv4Addresses = [];

        foreach ($records as $record) {
            $ip = $record['ip'] ?? $record['ipv6'] ?? null;

            if ($ip === null) {
                continue;
            }

            if (
                filter_var(
                    $ip,
                    FILTER_VALIDATE_IP,
                    FILTER_FLAG_NO_PRIV_RANGE |
                    FILTER_FLAG_NO_RES_RANGE
                ) === false
            ) {
                throw new RuntimeException(
                    'Hostname mengarah ke IP privat atau IP yang dicadangkan.'
                );
            }

            if (isset($record['ip'])) {
                $ipv4Addresses[] = $record['ip'];
            }
        }

        if (count($ipv4Addresses) === 0) {
            throw new RuntimeException(
                'Hostname harus memiliki alamat IPv4 publik.'
            );
        }

        return [$host, $ipv4Addresses[0], $port];
    }
}
