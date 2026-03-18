<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogRequestResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $traceId = bin2hex(random_bytes(4));
        
        \Illuminate\Support\Facades\Log::info("[REQUEST-$traceId] BEGIN", [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'input' => $request->except(['password', 'password_confirmation']),
        ]);

        $response = $next($request);

        $logData = [
            'status' => $response->getStatusCode(),
        ];

        if ($response->getStatusCode() >= 500) {
            $logData['content_preview'] = substr($response->getContent(), 0, 1000);
            \Illuminate\Support\Facades\Log::error("[RESPONSE-$traceId] ERROR", $logData);
        } else {
            \Illuminate\Support\Facades\Log::info("[RESPONSE-$traceId] END", $logData);
        }

        return $response;
    }
}
