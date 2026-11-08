<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireWorkerToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = config('infrawatch.worker_token');

        abort_if(! is_string($token) || $token === '' || ! hash_equals($token, (string) $request->header('X-Worker-Token')), 401, 'Invalid worker token.');

        return $next($request);
    }
}
