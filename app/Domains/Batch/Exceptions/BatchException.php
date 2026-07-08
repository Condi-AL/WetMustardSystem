<?php

namespace App\Domains\Batch\Exceptions;

use RuntimeException;

/**
 * Raised for batch record rule violations such as a missing required batch-size
 * variant selection or attempting to complete a batch with outstanding issues.
 */
class BatchException extends RuntimeException
{
    /** @var array<int, string> */
    public array $issues = [];

    /**
     * @param  array<int, string>  $issues
     */
    public static function withIssues(array $issues): self
    {
        $exception = new self(
            'Batch cannot be completed: '.count($issues).' outstanding issue(s).'
        );
        $exception->issues = array_values($issues);

        return $exception;
    }
}
