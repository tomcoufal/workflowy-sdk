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
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'] ?? '',
            note: $data['note'] ?? null,
            isCompleted: $data['isCompleted'] ?? false,
            createdAt: new DateTimeImmutable($data['createdAt'] ?? 'now'),
            modifiedAt: new DateTimeImmutable($data['modifiedAt'] ?? 'now'),
            completedAt: isset($data['completedAt']) ? new DateTimeImmutable($data['completedAt']) : null,
            layoutMode: isset($data['layoutMode']) ? LayoutMode::tryFrom($data['layoutMode']) : null,
            children: isset($data['children']) 
                ? array_map(fn(array $child) => self::fromArray($child), $data['children'])
                : []
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
        ];
    }
}
