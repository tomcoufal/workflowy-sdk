<?php

declare(strict_types=1);

namespace Workflowy\DTO;

readonly class TargetData
{
    public function __construct(
        public string $key,
        public string $type,
        public string $nodeName,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            key: $data['key'],
            type: $data['type'],
            nodeName: $data['name'] ?? '',
        );
    }
}
