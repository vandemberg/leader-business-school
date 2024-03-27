<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminApiAuth
{
    private string $key = 'ASUHDUAHSDUHASUDHUSAHDUHASUHADSUHADU';

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->header('Authorization') !== 'Bearer ' . $this->key) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
