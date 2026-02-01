<?php

declare(strict_types=1);

namespace Workflowy\Enums;

enum LayoutMode: string
{
    case Bullets = 'bullets';
    case ToDo = 'todo';
    case Board = 'board';
    case H1 = 'h1';
    case H2 = 'h2';
    case H3 = 'h3';
    case CodeBlock = 'code-block';
    case QuoteBlock = 'quote-block';
    
    // Workflowy může přidat další v budoucnu
    public static function tryFromOrDefault(string $value): ?self
    {
        return self::tryFrom($value);
    }
}
