<?php

declare(strict_types=1);

namespace Workflowy\Resources;

use Workflowy\DTO\NodeData;
use Workflowy\WorkflowyClient;

readonly class Nodes
{
    public function __construct(private WorkflowyClient $client) {}

    /**
     * Fetch the entire project tree or a specific node subtree
     */
    public function get(string $nodeId = 'None'): NodeData
    {
        // 'tree' seems to be the standard endpoint for fetching content
        $response = $this->client->request('GET', 'tree', ['nodeId' => $nodeId]);
        
        // Adjust based on actual API response structure. 
        // Assuming response matches NodeData structure directly or wrapped.
        return NodeData::fromArray($response);
    }

    public function create(string $parentId, string $name, int $priority = 0, ?string $note = null): NodeData
    {
        $response = $this->client->request('POST', 'nodes', [
            'parentId' => $parentId,
            'name' => $name,
            'priority' => $priority,
            'note' => $note
        ]);

        return NodeData::fromArray($response);
    }

    public function update(string $id, array $attributes): NodeData
    {
        $response = $this->client->request('PATCH', "nodes/{$id}", $attributes);
        return NodeData::fromArray($response);
    }

    public function delete(string $id): void
    {
        $this->client->request('DELETE', "nodes/{$id}");
    }
    
    public function check(string $id): NodeData
    {
        return $this->update($id, ['isCompleted' => true]);
    }

    public function uncheck(string $id): NodeData
    {
         return $this->update($id, ['isCompleted' => false]);
    }
}
