<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Models\TelegramUser;
use Illuminate\Http\JsonResponse;
use Telegram\Bot\Objects\Update;

abstract class TelegramBotBaseController extends Controller
{
    protected array $commands = [
        '/start',
        '/help',
        '/admin',
        '/profile',
        '/chekyuborish',
    ];
    protected function respondSuccess(): JsonResponse
    {
        return response()->json(['status' => 'ok'], 200);
    }

    protected function objectType(Update $update): ?string
    {
        $type = $update->objectType();

        return match ($type) {
            'message' => $this->{$type}($update),
            'callback_query' => $type,
            default => null,
        };

    }

    private function message(Update &$update): string
    {
        $message = $update->getMessage();

        $is_requesting_receipt=TelegramUser::getLastMessage()==='chekyuborish' ? true : false;

        $type = ($message->has('photo') || $message->has('document')) && $is_requesting_receipt  ? 'file' : 'message';

        if ($type === 'message' && in_array($message->getText(), $this->commands)) {

            return 'command';

        }

        if ('file' !== $type) {
            TelegramUser::clearLastMessage();
        }

        return $type;
    }
}
