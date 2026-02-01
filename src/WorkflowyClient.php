<?php

declare(strict_types=1);

namespace Workflowy;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
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
            throw new WorkflowyException("API Error {$statusCode} on {$method} {$uri}: {$body}", $statusCode);
        }

        if (empty($body)) {
            return [];
        }

        return json_decode($body, true, 512, JSON_THROW_ON_ERROR);
    }
}
