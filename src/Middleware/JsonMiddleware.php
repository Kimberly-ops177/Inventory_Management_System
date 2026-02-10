<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Http\Request;
use App\Http\Response;

class JsonMiddleware implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        // Ensure Content-Type is set for JSON responses
        header('Content-Type: application/json');

        // Continue to next middleware
        return $next($request);
    }
}
