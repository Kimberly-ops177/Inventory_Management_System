<?php
declare(strict_types=1);

namespace App;

class Router
{
    /** @var array<string, array<string, array{pattern: string, handler: callable, middleware: array}>> */
    private array $routes = [];

    public function get(string $path, callable $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, callable $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put(string $path, callable $handler, array $middleware = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, callable $handler, array $middleware = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    private function addRoute(string $method, string $path, callable $handler, array $middleware): void
    {
        $pattern = $this->convertPathToPattern($path);
        $this->routes[$method][$path] = [
            'pattern' => $pattern,
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }

    private function convertPathToPattern(string $path): string
    {
        // Convert /products/:id to regex pattern /products/([^/]+)
        $pattern = preg_replace('/:([a-zA-Z_][a-zA-Z0-9_]*)/', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    private function extractParamNames(string $path): array
    {
        preg_match_all('/:([a-zA-Z_][a-zA-Z0-9_]*)/', $path, $matches);
        return $matches[1] ?? [];
    }

    public function dispatch(string $method, string $path): string
    {
        $normalizedPath = parse_url($path, PHP_URL_PATH) ?: '/';

        // Try exact match first (for backward compatibility)
        if (isset($this->routes[$method][$normalizedPath])) {
            $route = $this->routes[$method][$normalizedPath];
            return $this->executeRoute($route, []);
        }

        // Try pattern matching
        foreach ($this->routes[$method] ?? [] as $routePath => $route) {
            if (preg_match($route['pattern'], $normalizedPath, $matches)) {
                array_shift($matches); // Remove full match
                $paramNames = $this->extractParamNames($routePath);
                $params = array_combine($paramNames, $matches) ?: [];
                return $this->executeRoute($route, $params);
            }
        }

        http_response_code(404);
        return json_encode(['message' => 'Not Found']);
    }

    private function executeRoute(array $route, array $params): string
    {
        $handler = $route['handler'];
        $middleware = $route['middleware'];

        // Execute middleware chain
        foreach ($middleware as $mw) {
            $result = $mw();
            if ($result !== null) {
                // Middleware returned a response, stop execution
                if (is_array($result) || is_object($result)) {
                    header('Content-Type: application/json');
                    return json_encode($result);
                }
                return (string) $result;
            }
        }

        // Execute handler with parameters
        $result = $handler(...array_values($params));

        if (is_array($result) || is_object($result)) {
            header('Content-Type: application/json');
            return json_encode($result);
        }

        return (string) $result;
    }
}
