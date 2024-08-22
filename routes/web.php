<?php

use Illuminate\Support\Facades\Route;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Http\Controllers\Telegram\TelegramBotController;
use App\Http\Middleware\TelegramMiddleware;
use Filament\Facades\Filament;

Route::get('/set-webhook', function () {

    if (!(Filament::auth()->check())) {

        return response()->json([
            'message' => 'unauthorized',
        ], 403);
    }

    $url = config('app.url') . "/bot/JWOaF3FJrqvt4kDYPVlx";

    $res = Telegram::setWebhook([
        'url' => $url,
        'secret_token' => config('telegram.bots.mybot.secret_token')
    ]);

    Telegram::commandsHandler(true);

    return response()->json([
        'response' => $res
    ]);
});

Route::get('/get-me', function () {

    if (!(Filament::auth()->check())) {

        return response()->json([
            'message' => 'unauthorized',
        ], 403);
    }

    return response()->json(['me' => Telegram::getMe()]);
});

Route::get('/get-webhook-info', function () {

    if (!(Filament::auth()->check())) {

        return response()->json([
            'message' => 'unauthorized',
        ], 403);
    }

    return Telegram::getWebhookInfo();
});


Route::post('/bot/JWOaF3FJrqvt4kDYPVlx', [TelegramBotController::class, 'handleWebhook'])->middleware(TelegramMiddleware::class);