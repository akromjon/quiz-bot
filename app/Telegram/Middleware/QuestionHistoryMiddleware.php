<?php

namespace App\Telegram\Middleware;

use App\Models\Question;
use App\Models\TelegramUser;

class QuestionHistoryMiddleware extends BaseMiddleware
{
    public static function handle(TelegramUser $user, int $sub_category_id, ?int $question_id): void
    {
        $history = $user->histories()->where('sub_category_id', $sub_category_id)->first();

        if (empty($history)) {

            $user->histories()->create([
                'sub_category_id' => $sub_category_id,
                'question_id' => $question_id,
            ]);

            return;
        }

        $history->update([
            'question_id' => $question_id
        ]);

    }
}
