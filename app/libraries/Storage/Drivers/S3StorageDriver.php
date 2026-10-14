<?php

namespace NovaFlow\Core\Storage\Drivers;

use NovaFlow\Core\Storage\Contracts\StorageDriverInterface;

/**
 * AWS S3 Compatible Storage Driver (Supports AWS S3, Cloudflare R2, MinIO, Wasabi)
 * Uses native lightweight HTTP REST requests with AWS SigV4 authentication.
 */
class S3StorageDriver implements StorageDriverInterface
{
    protected string $key;
    protected string $secret;
    protected string $region;
    protected string $bucket;
    protected string $endpoint;
    protected ?string $customUrl;

    public function __construct(array $config = [])
    {
        $this->key = $config['key'] ?? '';
        $this->secret = $config['secret'] ?? '';
        $this->region = $config['region'] ?? 'us-east-1';
        $this->bucket = $config['bucket'] ?? '';
        $this->endpoint = rtrim($config['endpoint'] ?? "https://{$this->bucket}.s3.{$this->region}.amazonaws.com", '/');
        $this->customUrl = isset($config['url']) ? rtrim($config['url'], '/') : null;
    }

    protected function getUrl(string $path): string
    {
        $clean = ltrim(str_replace('\\', '/', $path), '/');
        return "{$this->endpoint}/{$clean}";
    }

    public function url(string $path): string
    {
        if ($this->customUrl) {
            $clean = ltrim(str_replace('\\', '/', $path), '/');
            return "{$this->customUrl}/{$clean}";
        }
        return $this->getUrl($path);
    }

    public function put(string $path, string $contents, array $options = []): bool
    {
        $url = $this->getUrl($path);
        $headers = [
            'Content-Type' => $options['mimetype'] ?? 'application/octet-stream',
        ];

        $response = $this->sendRequest('PUT', $url, $headers, $contents);
        return $response['status'] >= 200 && $response['status'] < 300;
    }

    public function get(string $path): ?string
    {
        $url = $this->getUrl($path);
        $response = $this->sendRequest('GET', $url);

        if ($response['status'] === 200) {
            return $response['body'];
        }

        return null;
    }

    public function exists(string $path): bool
    {
        $url = $this->getUrl($path);
        $response = $this->sendRequest('HEAD', $url);
        return $response['status'] === 200;
    }

    public function missing(string $path): bool
    {
        return !$this->exists($path);
    }

    public function delete(string|array $paths): bool
    {
        $success = true;
        foreach ((array) $paths as $path) {
            $url = $this->getUrl($path);
            $res = $this->sendRequest('DELETE', $url);
            if ($res['status'] !== 204 && $res['status'] !== 200) {
                $success = false;
            }
        }
        return $success;
    }

    public function copy(string $from, string $to): bool
    {
        $url = $this->getUrl($to);
        $cleanFrom = ltrim(str_replace('\\', '/', $from), '/');
        $headers = [
            'x-amz-copy-source' => "/{$this->bucket}/{$cleanFrom}"
        ];

        $res = $this->sendRequest('PUT', $url, $headers);
        return $res['status'] === 200;
    }

    public function move(string $from, string $to): bool
    {
        if ($this->copy($from, $to)) {
            return $this->delete($from);
        }
        return false;
    }

    public function size(string $path): int
    {
        $url = $this->getUrl($path);
        $response = $this->sendRequest('HEAD', $url);
        return isset($response['headers']['content-length']) ? (int) $response['headers']['content-length'] : 0;
    }

    public function lastModified(string $path): int
    {
        $url = $this->getUrl($path);
        $response = $this->sendRequest('HEAD', $url);
        return isset($response['headers']['last-modified']) ? strtotime($response['headers']['last-modified']) : 0;
    }

    public function mimeType(string $path): ?string
    {
        $url = $this->getUrl($path);
        $response = $this->sendRequest('HEAD', $url);
        return $response['headers']['content-type'] ?? null;
    }

    public function makeDirectory(string $path): bool
    {
        return $this->put(rtrim($path, '/') . '/', '');
    }

    public function deleteDirectory(string $path): bool
    {
        return $this->delete(rtrim($path, '/') . '/');
    }

    public function files(string $directory = '', bool $recursive = false): array
    {
        // Simple file listing fallback
        return [];
    }

    public function append(string $path, string $data): bool
    {
        $existing = $this->get($path) ?? '';
        return $this->put($path, $existing . $data);
    }

    public function prepend(string $path, string $data): bool
    {
        $existing = $this->get($path) ?? '';
        return $this->put($path, $data . $existing);
    }

    /**
     * Send HTTP request signed with AWS Signature Version 4
     */
    protected function sendRequest(string $method, string $url, array $headers = [], ?string $body = null): array
    {
        $parsedUrl = parse_url($url);
        $host = $parsedUrl['host'];
        $path = $parsedUrl['path'] ?? '/';

        $timestamp = gmdate('Ymd\THis\Z');
        $date = gmdate('Ymd');

        $headers['Host'] = $host;
        $headers['x-amz-date'] = $timestamp;
        $headers['x-amz-content-sha256'] = hash('sha256', $body ?? '');

        // Generate SigV4 authorization header if credentials exist
        if ($this->key && $this->secret) {
            $authorization = $this->generateAuthorizationHeader($method, $path, $headers, $body ?? '', $timestamp, $date);
            $headers['Authorization'] = $authorization;
        }

        $formattedHeaders = [];
        foreach ($headers as $k => $v) {
            $formattedHeaders[] = "{$k}: {$v}";
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $formattedHeaders);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        if ($method === 'PUT' || $method === 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body ?? '');
        }

        $rawResponse = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        $responseHeaders = [];
        $responseBody = '';

        if ($rawResponse !== false) {
            $headerStr = substr($rawResponse, 0, $headerSize);
            $responseBody = substr($rawResponse, $headerSize);

            foreach (explode("\r\n", $headerStr) as $line) {
                if (strpos($line, ':') !== false) {
                    list($hk, $hv) = explode(':', $line, 2);
                    $responseHeaders[strtolower(trim($hk))] = trim($hv);
                }
            }
        }

        return [
            'status' => $statusCode,
            'headers' => $responseHeaders,
            'body' => $responseBody
        ];
    }

    /**
     * Generate AWS Signature Version 4 Authorization Header
     */
    protected function generateAuthorizationHeader(string $method, string $path, array $headers, string $body, string $timestamp, string $date): string
    {
        $canonicalHeaders = '';
        $signedHeadersArr = [];

        ksort($headers);
        foreach ($headers as $k => $v) {
            $lk = strtolower(trim($k));
            $canonicalHeaders .= "{$lk}:" . trim($v) . "\n";
            $signedHeadersArr[] = $lk;
        }
        $signedHeaders = implode(';', $signedHeadersArr);

        $payloadHash = hash('sha256', $body);
        $canonicalRequest = "{$method}\n{$path}\n\n{$canonicalHeaders}\n{$signedHeaders}\n{$payloadHash}";

        $scope = "{$date}/{$this->region}/s3/aws4_request";
        $stringToSign = "AWS4-HMAC-SHA256\n{$timestamp}\n{$scope}\n" . hash('sha256', $canonicalRequest);

        $kSecret = 'AWS4' . $this->secret;
        $kDate = hash_hmac('sha256', $date, $kSecret, true);
        $kRegion = hash_hmac('sha256', $this->region, $kDate, true);
        $kService = hash_hmac('sha256', 's3', $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);

        $signature = hash_hmac('sha256', $stringToSign, $kSigning);

        return "AWS4-HMAC-SHA256 Credential={$this->key}/{$scope}, SignedHeaders={$signedHeaders}, Signature={$signature}";
    }
}
