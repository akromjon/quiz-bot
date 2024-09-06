<?php

namespace App\Http\Middleware;

use App\Models\TelegramUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramUserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $update = Telegram::getWebhookUpdate();

        $user = TelegramUser::createOrUpdate($update->getChat());

        if ($user->status === 'blocked') {

            Log::error("User is blocked", $user->toArray());

            return response()->json([
                'message' => 'You are unauthorized!',
            ], 200);
        }


        return $next($request);
    }
}