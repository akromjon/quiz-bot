<?php

use Illuminate\Support\Facades\Route;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Http\Controllers\TelegramBotController;


Route::any('/bot/JWOaF3FJrqvt4kDYPVlx', [TelegramBotController::class, 'handleWebhook']);

Route::get('/set-webhook', function () {

    $url = config('app.url') . "/bot/JWOaF3FJrqvt4kDYPVlx";

    $s = Telegram::setWebhook(['url' => $url]);

    Telegram::commandsHandler(true);

    return response()->json([
        'status' => $s
    ]);
});

Route::get('/get-updates', function () {
    return Telegram::getWebhookUpdate();
});

Route::get('/get-me', function () {
    return response()->json(['me' => Telegram::getMe()]);
});


Route::get('/get-webhook-info', function () {


    return Telegram::getWebhookInfo();
});
