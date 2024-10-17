<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramUserQuestionResult extends Model
{
    use HasFactory;

    public static function storeUserQuestionResult(int $sub_category_id, int $question_id, bool $is_correct, int $page_number): self
    {
        return self::updateOrCreate([
            'telegram_user_id' => currentTelegramUser()->id,
            'sub_category_id' => $sub_category_id,
            'question_id' => $question_id,
        ], [
            'is_correct' => $is_correct,
            'page_number' => $page_number
        ]);
    }


}
