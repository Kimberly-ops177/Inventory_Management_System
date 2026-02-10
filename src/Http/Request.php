<?php
declare(strict_types=1);

namespace App\Http;

class Request
{
    private array $query;
    private array $request;
    private array $server;
    private ?array $json = null;

    public function __construct()
    {
        $this->query = $_GET;
        $this->request = $_POST;
        $this->server = $_SERVER;

        // Parse JSON body if content-type is application/json
        $contentType = $this->header('Content-Type') ?? '';
        if (str_contains($contentType, 'application/json')) {
            $input = file_get_contents('php://input');
            $this->json = $input ? json_decode($input, true) : [];
        }
    }

    public function getMethod(): string
    {
        return $this->server['REQUEST_METHOD'] ?? 'GET';
    }

    public function getPath(): string
    {
        return parse_url($this->server['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->request[$key] ?? $this->json[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post($key) ?? $this->get($key) ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->request, $this->json ?? []);
    }

    public function only(array $keys): array
    {
        $all = $this->all();
        return array_intersect_key($all, array_flip($keys));
    }

    public function header(string $key, mixed $default = null): mixed
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $this->server[$key] ?? $default;
    }

    public function isJson(): bool
    {
        return $this->json !== null;
    }

    public function isMethod(string $method): bool
    {
        return strtoupper($method) === $this->getMethod();
    }

    public function has(string ...$keys): bool
    {
        $all = $this->all();
        foreach ($keys as $key) {
            if (!isset($all[$key])) {
                return false;
            }
        }
        return true;
    }

    public function validate(array $rules): array
    {
        // Will be implemented with Validator class
        return [];
    }
}
