<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TelegramMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->header('x-telegram-bot-api-secret-token') !== config('telegram.bots.mybot.secret_token')) {
            
            Log::error("Someone is requesting with the wrong x-telegram-bot-api-secret-token or no token");
    
            return response()->json([
                'message' => 'You are unauthorized!',
            ], 403);
        }
    
        return $next($request);

    }
}
