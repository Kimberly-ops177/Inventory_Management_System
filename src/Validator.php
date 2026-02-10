<?php
declare(strict_types=1);

namespace App;

use PDO;

class Validator
{
    private array $errors = [];
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public static function make(array $data, array $rules): self
    {
        $validator = new self();
        $validator->validate($data, $rules);
        return $validator;
    }

    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];

        foreach ($rules as $field => $ruleString) {
            $ruleList = explode('|', $ruleString);
            $value = $data[$field] ?? null;

            foreach ($ruleList as $rule) {
                $this->applyRule($field, $value, $rule, $data);
            }
        }

        return empty($this->errors);
    }

    private function applyRule(string $field, mixed $value, string $rule, array $data): void
    {
        // Parse rule with parameters (e.g., "min:3" or "unique:users,email")
        if (str_contains($rule, ':')) {
            [$ruleName, $params] = explode(':', $rule, 2);
            $params = explode(',', $params);
        } else {
            $ruleName = $rule;
            $params = [];
        }

        match ($ruleName) {
            'required' => $this->validateRequired($field, $value),
            'email' => $this->validateEmail($field, $value),
            'numeric' => $this->validateNumeric($field, $value),
            'integer' => $this->validateInteger($field, $value),
            'min' => $this->validateMin($field, $value, (int)$params[0]),
            'max' => $this->validateMax($field, $value, (int)$params[0]),
            'unique' => $this->validateUnique($field, $value, $params[0], $params[1] ?? $field, $params[2] ?? null),
            'exists' => $this->validateExists($field, $value, $params[0], $params[1] ?? $field),
            default => null
        };
    }

    private function validateRequired(string $field, mixed $value): void
    {
        if ($value === null || $value === '' || $value === []) {
            $this->errors[$field][] = "The $field field is required.";
        }
    }

    private function validateEmail(string $field, mixed $value): void
    {
        if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = "The $field must be a valid email address.";
        }
    }

    private function validateNumeric(string $field, mixed $value): void
    {
        if ($value && !is_numeric($value)) {
            $this->errors[$field][] = "The $field must be a number.";
        }
    }

    private function validateInteger(string $field, mixed $value): void
    {
        if ($value && !filter_var($value, FILTER_VALIDATE_INT)) {
            $this->errors[$field][] = "The $field must be an integer.";
        }
    }

    private function validateMin(string $field, mixed $value, int $min): void
    {
        if ($value !== null && $value !== '') {
            if (is_string($value) && strlen($value) < $min) {
                $this->errors[$field][] = "The $field must be at least $min characters.";
            } elseif (is_numeric($value) && $value < $min) {
                $this->errors[$field][] = "The $field must be at least $min.";
            }
        }
    }

    private function validateMax(string $field, mixed $value, int $max): void
    {
        if ($value !== null && $value !== '') {
            if (is_string($value) && strlen($value) > $max) {
                $this->errors[$field][] = "The $field must not exceed $max characters.";
            } elseif (is_numeric($value) && $value > $max) {
                $this->errors[$field][] = "The $field must not exceed $max.";
            }
        }
    }

    private function validateUnique(string $field, mixed $value, string $table, string $column, ?string $ignoreId): void
    {
        if (!$value) {
            return;
        }

        $query = "SELECT COUNT(*) FROM $table WHERE $column = ?";
        $params = [$value];

        if ($ignoreId) {
            $query .= " AND id != ?";
            $params[] = $ignoreId;
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $this->errors[$field][] = "The $field has already been taken.";
        }
    }

    private function validateExists(string $field, mixed $value, string $table, string $column): void
    {
        if (!$value) {
            return;
        }

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM $table WHERE $column = ?");
        $stmt->execute([$value]);
        $count = $stmt->fetchColumn();

        if ($count === 0) {
            $this->errors[$field][] = "The selected $field is invalid.";
        }
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
