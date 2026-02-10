<?php
declare(strict_types=1);

namespace App\Models;

class AuditLog extends Model
{
    protected static string $table = 'audit_logs';
    protected array $fillable = [
        'user_id',
        'action',
        'details'
    ];

    protected function hasTimestamps(): bool
    {
        return false; // audit_logs table only has created_at
    }

    public function user(): ?array
    {
        if (!isset($this->attributes['user_id'])) {
            return null;
        }

        $userModel = User::find((int)$this->attributes['user_id']);
        return $userModel?->toArray();
    }

    public function getDetailsArray(): array
    {
        if (!isset($this->attributes['details'])) {
            return [];
        }

        $details = $this->attributes['details'];

        if (is_string($details)) {
            return json_decode($details, true) ?? [];
        }

        return $details;
    }

    public static function log(int $userId, string $action, array $details = []): self
    {
        return self::create([
            'user_id' => $userId,
            'action' => $action,
            'details' => json_encode($details)
        ]);
    }
}
