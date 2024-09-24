<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Models\TelegramUser;
use Illuminate\Http\JsonResponse;
use Telegram\Bot\Objects\Update;
use Illuminate\Support\Collection;

abstract class TelegramBotBaseController extends Controller
{
    protected array $commands = [
        '/start',
        '/help',
        '/admin',
        '/profile',
        '/chekyuborish',
        '/terms',
        '/privacy',
    ];
    protected function respondSuccess(): JsonResponse
    {
        return response()->json(['status' => 'ok'], 200);
    }

    protected function objectType(Update $update): ?string
    {
        $type = $update->objectType();

        return match ($type) {
            'message' => $this->message($update),
            'callback_query' => $type,
            default => null,
        };

    }

    private function message(Update &$update): string
    {
        $message = $update->getMessage();

        $isRequestingReceipt = $this->isRequestingReceipt();

        $type = $this->determineMessageType($message, $isRequestingReceipt);

        if ($this->isCommand($type, $message)) {

            return 'command';

        }

        if ($this->isNotFile($type)) {

            $this->clearLastMessage();

        }

        return $type;
    }

    private function isRequestingReceipt(): bool
    {
        return TelegramUser::getLastMessage() === 'chekyuborish';
    }

    private function determineMessageType(Collection $message, bool &$isRequestingReceipt): string
    {
        return ($message->has('photo') || $message->has('document')) && $isRequestingReceipt ? 'file' : 'message';
    }

    private function isCommand(string $type, Collection $message): bool
    {
        return $type === 'message' && in_array($message->getText(), $this->commands);
    }

    private function isNotFile(string $type): bool
    {
        return $type !== 'file';
    }

    private function clearLastMessage(): void
    {
        TelegramUser::clearLastMessage();
    }
}
