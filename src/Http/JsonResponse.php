<?php
declare(strict_types=1);

namespace App\Http;

class JsonResponse extends Response
{
    public function __construct(mixed $data, int $statusCode = 200, array $headers = [])
    {
        $headers['Content-Type'] = 'application/json';
        $content = json_encode($data, JSON_THROW_ON_ERROR);
        parent::__construct($content, $statusCode, $headers);
    }

    public static function success(mixed $data, string $message = '', int $statusCode = 200): self
    {
        return new self([
            'success' => true,
            'data' => $data,
            'message' => $message
        ], $statusCode);
    }

    public static function error(string $error, array $errors = [], int $statusCode = 400): self
    {
        return new self([
            'success' => false,
            'error' => $error,
            'errors' => $errors
        ], $statusCode);
    }
}
