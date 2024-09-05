<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;


class TelegramBotBaseController extends Controller
{
    protected function respondSuccess(): JsonResponse
    {
        return response()->json(['status' => 'ok'], 200);
    }
}
