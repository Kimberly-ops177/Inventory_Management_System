<?php
declare(strict_types=1);

namespace App\Models;

use App\Database;
use PDO;

abstract class Model
{
    protected static string $table;
    protected static string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $guarded = ['id', 'created_at', 'updated_at'];
    protected array $attributes = [];
    protected bool $exists = false;

    protected PDO $db;

    public function __construct(array $attributes = [])
    {
        $this->db = Database::connection();
        $this->fill($attributes);
    }

    public static function all(string $orderBy = '', string $direction = 'ASC'): array
    {
        $instance = new static();
        $query = "SELECT * FROM " . static::$table;

        if ($orderBy) {
            $query .= " ORDER BY $orderBy $direction";
        }

        $stmt = $instance->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find(int $id): ?self
    {
        $instance = new static();
        $stmt = $instance->db->prepare(
            "SELECT * FROM " . static::$table . " WHERE " . static::$primaryKey . " = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        $model = new static();
        // Set all attributes directly, bypassing fillable/guarded for database reads
        $model->attributes = $data;
        $model->exists = true;
        return $model;
    }

    public static function where(string $column, mixed $value, string $operator = '='): array
    {
        $instance = new static();
        $stmt = $instance->db->prepare(
            "SELECT * FROM " . static::$table . " WHERE $column $operator ?"
        );
        $stmt->execute([$value]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function whereIn(string $column, array $values): array
    {
        if (empty($values)) {
            return [];
        }

        $instance = new static();
        $placeholders = str_repeat('?,', count($values) - 1) . '?';
        $stmt = $instance->db->prepare(
            "SELECT * FROM " . static::$table . " WHERE $column IN ($placeholders)"
        );
        $stmt->execute($values);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create(array $attributes): self
    {
        $model = new static($attributes);
        $model->save();
        return $model;
    }

    public function save(): bool
    {
        if ($this->exists) {
            return $this->performUpdate();
        }

        return $this->performInsert();
    }

    protected function performInsert(): bool
    {
        $fillableData = $this->getFillableData();

        if (empty($fillableData)) {
            return false;
        }

        // Add created_at timestamp if column exists
        if ($this->hasTimestamps()) {
            $fillableData['created_at'] = date('Y-m-d H:i:s');
        }

        $columns = array_keys($fillableData);
        $placeholders = str_repeat('?,', count($columns) - 1) . '?';

        $query = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            static::$table,
            implode(',', $columns),
            $placeholders
        );

        $stmt = $this->db->prepare($query);
        $result = $stmt->execute(array_values($fillableData));

        if ($result) {
            $this->attributes[static::$primaryKey] = (int)$this->db->lastInsertId();
            $this->exists = true;
        }

        return $result;
    }

    protected function performUpdate(): bool
    {
        $fillableData = $this->getFillableData();

        if (empty($fillableData)) {
            return false;
        }

        // Add updated_at timestamp if column exists
        if ($this->hasTimestamps()) {
            $fillableData['updated_at'] = date('Y-m-d H:i:s');
        }

        $sets = [];
        foreach (array_keys($fillableData) as $column) {
            $sets[] = "$column = ?";
        }

        $query = sprintf(
            "UPDATE %s SET %s WHERE %s = ?",
            static::$table,
            implode(', ', $sets),
            static::$primaryKey
        );

        $values = array_values($fillableData);
        $values[] = $this->attributes[static::$primaryKey];

        $stmt = $this->db->prepare($query);
        return $stmt->execute($values);
    }

    public function update(array $attributes): bool
    {
        $this->fill($attributes);
        return $this->save();
    }

    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }

        $stmt = $this->db->prepare(
            "DELETE FROM " . static::$table . " WHERE " . static::$primaryKey . " = ?"
        );

        return $stmt->execute([$this->attributes[static::$primaryKey]]);
    }

    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $this->attributes[$key] = $value;
            }
        }

        return $this;
    }

    protected function isFillable(string $key): bool
    {
        // If fillable is defined, only allow those fields
        if (!empty($this->fillable)) {
            return in_array($key, $this->fillable, true);
        }

        // Otherwise, allow everything except guarded fields
        return !in_array($key, $this->guarded, true);
    }

    protected function getFillableData(): array
    {
        $data = [];

        foreach ($this->attributes as $key => $value) {
            // Skip primary key and timestamps on insert
            if ($key === static::$primaryKey || in_array($key, ['created_at', 'updated_at'], true)) {
                continue;
            }

            if ($this->isFillable($key)) {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    protected function hasTimestamps(): bool
    {
        // Override in child class if table doesn't have timestamps
        return true;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function __get(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    public function __set(string $key, mixed $value): void
    {
        if ($this->isFillable($key)) {
            $this->attributes[$key] = $value;
        }
    }

    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }
}
