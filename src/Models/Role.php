<?php
declare(strict_types=1);

namespace App\Models;

class Role extends Model
{
    protected static string $table = 'roles';
    protected array $fillable = ['name'];

    protected function hasTimestamps(): bool
    {
        return false; // roles table only has created_at
    }
}
