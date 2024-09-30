<?php

namespace App\Telegram\Menu;

use Illuminate\Support\Facades\Cache;
use App\Models\Question;
use Telegram\Bot\Keyboard\Keyboard;


class FreeQuestionMenu extends Menu
{
    public static function get(int $page_number): array
    {
        $question = Cache::rememberForever("free_question:{$page_number}", function () use ($page_number): ?Question {
            return self::getFreeQuestionBySubCategoryId($page_number);
        });

        if (-1 === $page_number) {
            return self::base();
        }


        if ($question === null) {

            $keyboard = self::makeInlineKeyboard()
                ->row([
                    Keyboard::inlineButton([
                        'text' => '🏠 Asosiy Menyu',
                        'callback_data' => json_encode(['m' => 'base', 'id' => '']),
                    ])
                ]);


            $text = setting('free_quiz_finished_message') ?? '🏁 Testlar Tugadi 🏁';

            return [
                'type' => 'message',
                'text' => $text,
                'parse_mode' => 'HTML',
                'answerCallbackText' => '🏁 Testlar Tugadi 🏁',
                'reply_markup' => $keyboard,
            ];

        }

        $letters = ['a', 'b', 'c', 'd', 'e', 'f', 'g'];

        $keyboards = [];

        foreach ($question->questionOptions as $key => $option) {

            $keyboards[] = Keyboard::inlineButton([
                'text' => $letters[$key],
                'callback_data' => json_encode([
                    'm' => $option->is_answer ? 'F' : 'FW',
                    'p' => $page_number + 1
                ]),
            ]);
        }

        $keyboard = self::makeInlineKeyboard()
            ->row($keyboards)
            ->row([
                Keyboard::inlineButton([
                    'text' => '⬅️ Orqaga',
                    'callback_data' => json_encode(['m' => 'F', 'p' => $page_number === 1 ? -1 : $page_number - 1]),
                ])
            ])
            ->row(self::createFinishTestButton());

        return $question->file === null ?
            self::prepareMessageResponseForFreeQuiz($question, $keyboard) :
            self::prepareFileResponseForFreeQuiz($question, $keyboard);


    }

    protected static function getFreeQuestionBySubCategoryId(int $page_number = 1): ?Question
    {
        $questions = Question::where('is_free', true)
            ->active()
            ->with('questionOptions', 'subCategory.category')
            ->orderBy('id')
            ->paginate(1, '*', 'page', $page_number);


        return $questions->first();
    }


    protected static function prepareMessageResponseForFreeQuiz(Question $question, Keyboard $keyboard): array
    {
        return [
            'type' => 'message',
            'text' => self::formatQuestionForFreeQuiz($question),
            'parse_mode' => 'HTML',
            'reply_markup' => $keyboard,
        ];
    }

    protected static function prepareFileResponseForFreeQuiz(Question $question, Keyboard $keyboard): array
    {
        return [
            'type' => 'file',
            'reply_markup' => $keyboard,
            'parse_mode' => 'HTML',
            'file' => asset("/storage/{$question->file}"),
            'caption' => self::formatQuestionForFreeQuiz($question),
        ];
    }

    public static function handleWrongAnswer(): array
    {
        return [
            'text' => "Noto'g'ri ❌",
            'show_alert' => true,
        ];
    }

    protected static function formatQuestionForFreeQuiz(Question $question): string
    {
        $question_order = Cache::rememberForever("free_question_order_{$question->id}", function () use ($question) {

            return Question::where('is_free', true)->where('id', '<=', $question->id)->count();

        });

        $questions_count = Cache::rememberForever('free_questions_count', function () {

            return Question::where('is_free', true)->count();

        });

        $text = <<<TEXT
        <b>{$question_order}/{$questions_count}-SAVOL:</b>\n
        {$question->question}\n\n
        TEXT;
        $text .= implode("\n", $question->questionOptions->pluck('option')->toArray());

        return $text;
    }


}
