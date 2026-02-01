<?php

declare(strict_types=1);

namespace Workflowy;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Workflowy\Exceptions\NotFoundException;
use Workflowy\Exceptions\RateLimitException;
use Workflowy\Exceptions\ServerException;
use Workflowy\Exceptions\UnauthorizedException;
use Workflowy\Exceptions\ValidationException;
use Workflowy\Exceptions\WorkflowyException;
use Workflowy\Resources\Nodes;
use Workflowy\Resources\Targets;

class WorkflowyClient
{
    private const BASE_URI = 'https://workflowy.com/api/v1/'; // Corrected to v1 as per documentation

    public function __construct(
        private readonly string $apiKey,
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
    ) {}

    public function nodes(): Nodes
    {
        return new Nodes($this);
    }

    public function targets(): Targets
    {
        return new Targets($this);
    }

    /**
     * @throws WorkflowyException
     */
    public function request(string $method, string $path, array $data = []): array
    {
        $uri = self::BASE_URI . ltrim($path, '/');

        if ($method === 'GET' && !empty($data)) {
            $uri .= '?' . http_build_query($data);
        }
        
        $request = $this->requestFactory->createRequest($method, $uri)
            ->withHeader('Authorization', 'Bearer ' . $this->apiKey)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json');

        if ($method !== 'GET' && !empty($data)) {
            $json = json_encode($data, JSON_THROW_ON_ERROR);
            $request = $request->withBody($this->streamFactory->createStream($json));
        }

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (\Throwable $e) {
            throw new WorkflowyException("Network Error: " . $e->getMessage(), 0, $e);
        }

        $statusCode = $response->getStatusCode();
        $body = (string) $response->getBody();

        if ($statusCode >= 400) {
            $this->handleRequestError($statusCode, $body);
        }

        if (empty($body)) {
            return [];
        }

        return json_decode($body, true, 512, JSON_THROW_ON_ERROR);
    }

    private function handleRequestError(int $statusCode, string $body): void
    {
        // Try to parse error message from JSON
        $message = "API Error {$statusCode}";
        try {
            $json = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
            if (isset($json['error'])) {
                 $message = is_string($json['error']) ? $json['error'] : json_encode($json['error']);
            } elseif (isset($json['message'])) {
                 $message = $json['message'];
            }
        } catch (\Throwable) {
            // Fallback to raw body if not JSON or if parsing fails
            if (!empty($body)) {
                $message .= ": {$body}";
            }
        }

        match ($statusCode) {
            400 => throw new ValidationException($message, $statusCode),
            401 => throw new UnauthorizedException($message, $statusCode),
            403 => throw new UnauthorizedException($message, $statusCode), // Access denied is similar to unauthorized
            404 => throw new NotFoundException($message, $statusCode),
            429 => throw new RateLimitException($message, $statusCode),
            500, 502, 503, 504 => throw new ServerException($message, $statusCode),
            default => throw new WorkflowyException($message, $statusCode),
        };
    }
}
