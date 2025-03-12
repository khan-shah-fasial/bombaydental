<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIP
{
    // Allowed IPs list (you can add more IPs here)
    protected $allowedIps = [
        '103.175.61.38',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Get the visitor's IP address
        $ip = $request->ip();

        // Check if the IP is allowed
        if (!in_array($ip, $this->allowedIps)) {
            return response()->view('forbidden');
        }

        return $next($request);
    }
}