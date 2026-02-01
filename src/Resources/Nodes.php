<?php

declare(strict_types=1);

namespace Workflowy\Resources;

use Workflowy\DTO\NodeData;
use Workflowy\WorkflowyClient;

readonly class Nodes
{
    public function __construct(private WorkflowyClient $client) {}

    /**
     * Retrieves a node and its direct children.
     *
     * @param string $nodeId Node UUID or target key (e.g., 'inbox', 'home', 'None' for root)
     * @return NodeData The requested node with its children populated
     * @throws \Workflowy\Exceptions\NotFoundException If node is not found
     * @throws \Workflowy\Exceptions\WorkflowyException On other API errors
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

    /**
     * Creates a new node under a parent.
     *
     * @param string $parentId Parent node UUID or target key (e.g., 'inbox')
     * @param string $name The text content of the node
     * @param int $priority Position: 0 for top, any other value for bottom
     * @param string|null $note Optional note content
     * @return NodeData The newly created node
     * @throws \Workflowy\Exceptions\WorkflowyException If creation fails
     */
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

    /**
     * Updates an existing node's attributes.
     *
     * @param string $id Node UUID
     * @param array $attributes Associative array of attributes to update (e.g., ['name' => 'New Name', 'note' => 'New Note'])
     * @return NodeData The updated node
     * @throws \Workflowy\Exceptions\NotFoundException If node does not exist
     * @throws \Workflowy\Exceptions\WorkflowyException On other API errors
     */
    public function update(string $id, array $attributes): NodeData
    {
        // Endpoint: POST /nodes/:id
        $this->client->request('POST', "nodes/{$id}", $attributes);
        return $this->get($id);
    }

    /**
     * Permanently deletes a node.
     *
     * @param string $id Node UUID
     * @throws \Workflowy\Exceptions\NotFoundException If node does not exist
     * @throws \Workflowy\Exceptions\WorkflowyException On other API errors
     */
    public function delete(string $id): void
    {
        $this->client->request('DELETE', "nodes/{$id}");
    }
    
    /**
     * Marks a node as completed.
     *
     * @param string $id Node UUID
     * @return NodeData The updated node
     * @throws \Workflowy\Exceptions\NotFoundException If node does not exist
     * @throws \Workflowy\Exceptions\WorkflowyException On other API errors
     */
    public function check(string $id): NodeData
    {
        $this->client->request('POST', "nodes/{$id}/complete");
        return $this->get($id);
    }

    /**
     * Marks a node as incomplete (active).
     *
     * @param string $id Node UUID
     * @return NodeData The updated node
     * @throws \Workflowy\Exceptions\NotFoundException If node does not exist
     * @throws \Workflowy\Exceptions\WorkflowyException On other API errors
     */
    public function uncheck(string $id): NodeData
    {
        $this->client->request('POST', "nodes/{$id}/uncomplete");
        return $this->get($id);
    }
}
