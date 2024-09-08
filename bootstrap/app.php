<?php

use App\Models\TelegramUser;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Exceptions\TelegramOtherException;
use Telegram\Bot\Exceptions\TelegramResponseException;
use Telegram\Bot\Laravel\Facades\Telegram;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            'bot/JWOaF3FJrqvt4kDYPVlx'
        ]);

        $middleware->trustProxies(at: '*');
    })
    
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->report(function (TelegramResponseException $e) {

            if(403===$e->getCode()) {

                $update=getWebhookUpdate();

                $user_id=$update->getChat()->getId();

                if(is_int($user_id)) {
                    
                    TelegramUser::where('user_id', $user_id)->update(['status'=>'blocked']);
                }
                
                Log::error("User $user_id is blocked the bot");

                return response()->json([
                    'message' => 'unauthorized',
                ], 200);
            }

            // ...
        });

        // TelegramOtherException

        $exceptions->report(function (TelegramOtherException $e) {
           
            Log::error($e->getMessage());

            return response()->json([
                'message' => 'unauthorized',
            ], 200);


        });
    })->create();
