<?php

declare(strict_types=1);

namespace Workflowy\DTO;

use DateTimeImmutable;
use Workflowy\Enums\LayoutMode;

readonly class NodeData
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $note,
        public bool $isCompleted,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $modifiedAt,
        public ?DateTimeImmutable $completedAt = null,
        public ?LayoutMode $layoutMode = null,
        /** @var NodeData[] */
        public array $children = [],
        public int $priority = 0,
    ) {}

    public static function fromArray(array $data): self
    {
        $createdAtTimestamp = isset($data['createdAt']) ? (int)$data['createdAt'] : time();
        $modifiedAtTimestamp = isset($data['modifiedAt']) ? (int)$data['modifiedAt'] : time();
        $completedAtTimestamp = isset($data['completedAt']) ? (int)$data['completedAt'] : null;

        $createdAt = DateTimeImmutable::createFromFormat('U', (string)$createdAtTimestamp) ?: new DateTimeImmutable();
        $modifiedAt = DateTimeImmutable::createFromFormat('U', (string)$modifiedAtTimestamp) ?: new DateTimeImmutable();
        $completedAt = $completedAtTimestamp ? (DateTimeImmutable::createFromFormat('U', (string)$completedAtTimestamp) ?: null) : null;

        $layoutModeStr = $data['data']['layoutMode'] ?? $data['layoutMode'] ?? null;

        return new self(
            id: $data['id'] ?? 'unknown',
            name: $data['name'] ?? '',
            note: $data['note'] ?? null,
            isCompleted: $completedAt !== null,
            createdAt: $createdAt,
            modifiedAt: $modifiedAt,
            completedAt: $completedAt,
            layoutMode: $layoutModeStr ? LayoutMode::tryFrom($layoutModeStr) : null,
            children: isset($data['children']) 
                ? array_map(fn(array $child) => self::fromArray($child), $data['children'])
                : [],
            priority: isset($data['priority']) ? (int)$data['priority'] : 0,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'note' => $this->note,
            'isCompleted' => $this->isCompleted,
            'layoutMode' => $this->layoutMode?->value,
            'createdAt' => $this->createdAt->getTimestamp(),
            'modifiedAt' => $this->modifiedAt->getTimestamp(),
            'completedAt' => $this->completedAt?->getTimestamp(),
            'priority' => $this->priority,
        ];
    }
}
