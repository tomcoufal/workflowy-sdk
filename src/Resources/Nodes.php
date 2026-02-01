<?php

declare(strict_types=1);

namespace Workflowy\Resources;

use Workflowy\DTO\NodeData;
use Workflowy\WorkflowyClient;

readonly class Nodes
{
    public function __construct(private WorkflowyClient $client) {}

    /**
     * Fetch a node and its children.
     * Use 'None' for root, or target keys like 'inbox', 'home'.
     */
    public function get(string $nodeId = 'None'): NodeData
    {
        // 1. Fetch children using List nodes endpoint
        $childrenResponse = $this->client->request('GET', 'nodes', ['parent_id' => $nodeId]);
        $childrenData = $childrenResponse['nodes'] ?? [];

        // 2. Try to fetch node details
        $nodeDetails = [
            'id' => $nodeId,
            'name' => $nodeId === 'None' ? 'Root' : ucfirst($nodeId),
            // Defaults will be handled by NodeData::fromArray
        ];

        if ($nodeId !== 'None') {
            try {
                // If nodeId is not a UUID (e.g. 'inbox'), this might fail or return error, 
                // but if it works for targets, great.
                // If it fails, we keep the default details.
                // We use a separate try-catch to ensure we at least return the children which is the main goal of get() here.
                $detailsResponse = $this->client->request('GET', "nodes/{$nodeId}");
                if (isset($detailsResponse['node'])) {
                    $nodeDetails = $detailsResponse['node'];
                }
            } catch (\Throwable $e) {
                // Ignore, assumes it's a virtual node or target without direct access
            }
        }

        // Attach children
        $nodeDetails['children'] = $childrenData;

        return NodeData::fromArray($nodeDetails);
    }

    public function create(string $parentId, string $name, int $priority = 0, ?string $note = null): NodeData
    {
        $response = $this->client->request('POST', 'nodes', [
            'parent_id' => $parentId,
            'name' => $name,
            'note' => $note,
            'position' => $priority === 0 ? 'top' : 'bottom'
        ]);

        // Response contains { "item_id": "..." }
        $newItemId = $response['item_id'] ?? null;
        
        if ($newItemId) {
            return $this->get($newItemId);
        }
        
        // Fallback if no ID returned (shouldn't happen on success)
        throw new \RuntimeException("Failed to create node, no item_id returned.");
    }

    public function update(string $id, array $attributes): NodeData
    {
        // Endpoint: POST /nodes/:id
        $this->client->request('POST', "nodes/{$id}", $attributes);
        return $this->get($id);
    }

    public function delete(string $id): void
    {
        $this->client->request('DELETE', "nodes/{$id}");
    }
    
    public function check(string $id): NodeData
    {
        $this->client->request('POST', "nodes/{$id}/complete");
        return $this->get($id);
    }

    public function uncheck(string $id): NodeData
    {
        $this->client->request('POST', "nodes/{$id}/uncomplete");
        return $this->get($id);
    }
}
