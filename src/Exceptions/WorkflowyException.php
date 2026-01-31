<?php

declare(strict_types=1);

namespace Workflowy\Exceptions;

use Exception;
use Psr\Http\Client\ClientExceptionInterface;

class WorkflowyException extends Exception implements ClientExceptionInterface
{
    public function __construct(string $message, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
