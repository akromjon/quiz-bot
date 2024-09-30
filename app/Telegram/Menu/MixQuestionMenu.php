<?php

namespace App\Telegram\Menu;

use Illuminate\Support\Facades\Cache;
use App\Models\Question;
use Telegram\Bot\Keyboard\Keyboard;


class MixQuestionMenu extends Menu
{
    public static function get(): array
    {
        $randomQuestion = Question::active()->inRandomOrder()->first();

        $letters = ['a', 'b', 'c', 'd', 'e', 'f', 'g'];

        $keyboards = [];

        foreach ($randomQuestion->questionOptions as $key => $option) {

            $keyboards[] = Keyboard::inlineButton([
                'text' => $letters[$key],
                'callback_data' => json_encode([
                    'q' => $randomQuestion->id,
                    'm' => $option->is_answer ? 'M' : 'W',
                    'id' => $option->id,
                ]),
            ]);
        }

        $keyboard = self::makeInlineKeyboard()
            ->row($keyboards)
            ->row(self::createFinishTestButton());

        return $randomQuestion->file === null ?
            self::prepareMessageResponse($randomQuestion, $keyboard) :
            self::prepareFileResponse($randomQuestion, $keyboard);
    }

    protected static function prepareMessageResponse(Question $randomQuestion, Keyboard $keyboard): array
    {
        return [
            'type' => 'message',
            'text' => self::formatMixQuestion($randomQuestion),
            'parse_mode' => 'HTML',
            'reply_markup' => $keyboard,
        ];
    }



    protected static function prepareFileResponse(Question $randomQuestion, Keyboard $keyboard): array
    {
        return [
            'type' => 'file',
            'reply_markup' => $keyboard,
            'parse_mode' => 'HTML',
            'file' => asset("/storage/{$randomQuestion->file}"),
            'caption' => self::formatMixQuestion($randomQuestion),
        ];
    }

    protected static function formatMixQuestion(Question $question): string
    {
        $sub_category = $question->subCategory;

        $questions_count = $sub_category->questionCount();

        $text = <<<TEXT
            <b>{$sub_category->category->trimmed_title}, {$sub_category->title}</b>\n
            {$question->number}/{$questions_count} - SAVOL:
            {$question->question}\n\n
            TEXT;
        $text .= implode("\n", $question->questionOptions->pluck('option')->toArray());

        return $text;
    }

}
