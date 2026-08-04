<?php

declare(strict_types=1);

function http_request_json(string $method, string $url, array $payload = [], array $headers = []): array
{
    $method = strtoupper($method);
    $headers[] = 'Accept: application/json';
    $body = $payload === [] ? null : json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

    if ($body !== null) {
        $headers[] = 'Content-Type: application/json';
    }

    if (function_exists('curl_init')) {
        $curl = curl_init($url);

        if ($curl === false) {
            throw new RuntimeException('Unable to initialize HTTP client.');
        }

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_POSTFIELDS => $body,
        ]);

        $response = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if ($response === false) {
            throw new RuntimeException('HTTP request failed: ' . $error);
        }
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", $headers),
                'content' => $body ?? '',
                'ignore_errors' => true,
                'timeout' => 20,
            ],
        ]);
        $response = file_get_contents($url, false, $context);
        $statusLine = $http_response_header[0] ?? 'HTTP/1.1 500';
        preg_match('/\s(\d{3})\s/', $statusLine, $matches);
        $status = isset($matches[1]) ? (int) $matches[1] : 500;

        if ($response === false) {
            throw new RuntimeException('HTTP request failed.');
        }
    }

    $decoded = json_decode($response, true);

    if (!is_array($decoded)) {
        throw new RuntimeException('Remote service returned an invalid JSON response.');
    }

    if ($status < 200 || $status >= 300) {
        $message = (string) ($decoded['error_description'] ?? $decoded['detail'] ?? $decoded['message'] ?? 'Remote service request failed.');
        throw new RuntimeException($message);
    }

    return $decoded;
}

function http_post_form(string $url, array $fields): array
{
    $body = http_build_query($fields, '', '&', PHP_QUERY_RFC3986);
    $headers = [
        'Accept: application/json',
        'Content-Type: application/x-www-form-urlencoded',
    ];

    if (function_exists('curl_init')) {
        $curl = curl_init($url);

        if ($curl === false) {
            throw new RuntimeException('Unable to initialize HTTP client.');
        }

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_FOLLOWLOCATION => false,
        ]);
        $response = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if ($response === false) {
            throw new RuntimeException('HTTP request failed: ' . $error);
        }
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headers),
                'content' => $body,
                'ignore_errors' => true,
                'timeout' => 20,
            ],
        ]);
        $response = file_get_contents($url, false, $context);
        $statusLine = $http_response_header[0] ?? 'HTTP/1.1 500';
        preg_match('/\s(\d{3})\s/', $statusLine, $matches);
        $status = isset($matches[1]) ? (int) $matches[1] : 500;

        if ($response === false) {
            throw new RuntimeException('HTTP request failed.');
        }
    }

    $decoded = json_decode($response, true);

    if (!is_array($decoded)) {
        throw new RuntimeException('Remote service returned an invalid JSON response.');
    }

    if ($status < 200 || $status >= 300) {
        throw new RuntimeException((string) ($decoded['error_description'] ?? $decoded['error'] ?? 'Remote service request failed.'));
    }

    return $decoded;
}
