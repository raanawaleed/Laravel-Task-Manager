<?php

namespace App\Exceptions;

use RuntimeException;

class ProjectException extends RuntimeException
{
    public static function notFound(int $id): self
    {
        return new self("Project with ID {$id} not found.", 404);
    }

    public static function createFailed(string $reason = ''): self
    {
        return new self("Failed to create project. {$reason}", 500);
    }

    public static function updateFailed(string $reason = ''): self
    {
        return new self("Failed to update project. {$reason}", 500);
    }

    public static function deleteFailed(string $reason = ''): self
    {
        return new self("Failed to delete project. {$reason}", 500);
    }
}
