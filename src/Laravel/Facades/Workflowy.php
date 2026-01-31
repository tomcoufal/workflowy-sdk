<?php

declare(strict_types=1);

namespace Workflowy\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Workflowy\Resources\Nodes nodes()
 * @method static \Workflowy\Resources\Targets targets()
 * @see \Workflowy\WorkflowyClient
 */
class Workflowy extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'workflowy';
    }
}
