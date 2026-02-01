<?php

declare(strict_types=1);

namespace Workflowy\Resources;

use Workflowy\DTO\TargetData;
use Workflowy\WorkflowyClient;

readonly class Targets
{
    public function __construct(private WorkflowyClient $client) {}

    /**
     * Lists all available targets (both system and user-defined shortcuts).
     *
     * @return TargetData[] List of available targets
     * @throws \Workflowy\Exceptions\WorkflowyException If API request fails
     */
    public function list(): array
    {
        $response = $this->client->request('GET', 'targets');
        
        // Assuming response is an array of targets
        return array_map(
            fn(array $item) => TargetData::fromArray($item),
            $response['targets'] ?? []
        );
    }
}
