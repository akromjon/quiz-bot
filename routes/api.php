<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Telegram\TelegramBotController;
use App\Http\Middleware\TelegramMiddleware;
use App\Http\Middleware\TelegramUserMiddleware;


Route::post('/bot/JWOaF3FJrqvt4kDYPVlx', [TelegramBotController::class, 'handleWebhook'])
    ->withoutMiddleware(['web'])
    ->middleware([TelegramMiddleware::class, TelegramUserMiddleware::class]);

