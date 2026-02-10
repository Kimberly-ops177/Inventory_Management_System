<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Http\Request;
use App\Http\Response;
use App\Http\JsonResponse;

class AuthMiddleware implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (!isset($_SESSION['user'])) {
            // Check if it's an API request or web request
            if ($request->isJson() || str_starts_with($request->getPath(), '/api/')) {
                return JsonResponse::error('Unauthenticated', [], 401);
            }

            // Redirect to login for web requests
            header('Location: /login');
            return new Response('', 302);
        }

        return $next($request);
    }
}
