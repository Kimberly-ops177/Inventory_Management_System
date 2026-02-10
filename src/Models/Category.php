<?php
declare(strict_types=1);

namespace App\Models;

class Category extends Model
{
    protected static string $table = 'categories';
    protected array $fillable = ['name', 'description'];

    protected function hasTimestamps(): bool
    {
        return false; // categories table only has created_at
    }

    public function products(): array
    {
        if (!isset($this->attributes['id'])) {
            return [];
        }

        return Product::where('category_id', $this->attributes['id']);
    }
}
