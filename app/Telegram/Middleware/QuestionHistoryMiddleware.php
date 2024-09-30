<?php

namespace App\Telegram\Middleware;

use App\Models\Question;
use App\Models\SubCategory;
use App\Models\TelegramUser;

class QuestionHistoryMiddleware extends BaseMiddleware
{
    public static function handle(TelegramUser $user, int $sub_category_id, ?int $question_id, ?int $page_number): void
    {
        $history = $user->histories()->where('sub_category_id', $sub_category_id)->first();

        if (empty($history)) {

            $user->histories()->create([
                'sub_category_id' => $sub_category_id,
                'question_id' => $question_id,
                'page_number' => $page_number,
            ]);

            $sub_category = SubCategory::find(id: $sub_category_id);

            $user->results()->create([
                'type' => 'topical',
                'sub_category_id' => $sub_category_id,
                'total_questions' => $sub_category->questionCount(),
            ]);

            return;
        }

        $history->update([
            'question_id' => $question_id,
            'page_number' => $page_number
        ]);

    }
}
