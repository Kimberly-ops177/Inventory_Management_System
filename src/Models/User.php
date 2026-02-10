<?php
declare(strict_types=1);

namespace App\Models;

class User extends Model
{
    protected static string $table = 'users';
    protected array $fillable = ['role_id', 'name', 'email', 'password_hash', 'is_active'];

    public function setPassword(string $password): void
    {
        $this->attributes['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->attributes['password_hash'] ?? '');
    }

    public function isActive(): bool
    {
        return ($this->attributes['is_active'] ?? 0) == 1;
    }

    public function role(): ?array
    {
        if (!isset($this->attributes['role_id'])) {
            return null;
        }

        $roleModel = Role::find((int)$this->attributes['role_id']);
        return $roleModel?->toArray();
    }
}
