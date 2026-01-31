<?php

declare(strict_types=1);

namespace Workflowy\Enums;

enum LayoutMode: string
{
    case Bullets = 'bullets';
    case Board = 'board';
    case ToDo = 'todo';
    
    // Workflowy může přidat další v budoucnu
    public static function tryFromOrDefault(string $value): ?self
    {
        return self::tryFrom($value);
    }
}
