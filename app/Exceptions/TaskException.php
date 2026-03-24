<?php

namespace App\Exceptions;

use RuntimeException;

class TaskException extends RuntimeException
{
    public static function notFound(int $id): self
    {
        return new self("Task with ID {$id} not found.", 404);
    }

    public static function createFailed(string $reason = ''): self
    {
        return new self("Failed to create task. {$reason}", 500);
    }

    public static function updateFailed(string $reason = ''): self
    {
        return new self("Failed to update task. {$reason}", 500);
    }

    public static function deleteFailed(string $reason = ''): self
    {
        return new self("Failed to delete task. {$reason}", 500);
    }

    public static function reorderFailed(): self
    {
        return new self('Failed to reorder tasks.', 500);
    }
}
