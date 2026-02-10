<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Http\Request;
use App\Http\Response;
use App\Http\JsonResponse;

class RoleMiddleware implements Middleware
{
    private array $allowedRoles;

    public function __construct(array $allowedRoles = ['admin'])
    {
        $this->allowedRoles = $allowedRoles;
    }

    public function handle(Request $request, callable $next): Response
    {
        $user = $_SESSION['user'] ?? null;

        if (!$user || !in_array($user['role'], $this->allowedRoles, true)) {
            if ($request->isJson() || str_starts_with($request->getPath(), '/api/')) {
                return JsonResponse::error('Forbidden', [], 403);
            }

            http_response_code(403);
            return new Response('Forbidden', 403);
        }

        return $next($request);
    }
}
