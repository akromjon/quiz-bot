<?php

namespace App\Telegram\Menu;

use App\Telegram\Middleware\QuestionHistoryMiddleware;
use Illuminate\Support\Facades\Cache;
use Telegram\Bot\Keyboard\Keyboard;
use App\Models\SubCategory;
use App\Models\Question;


class QuestionMenu extends Menu
{
    public static function get(int $sub_category_id, int $page_number = 1): array
    {
        $question = Cache::rememberForever("question:{$sub_category_id}:{$page_number}", function () use ($sub_category_id, $page_number): ?Question {

            return self::getQuestionBySubCategoryId(
                sub_category_id: $sub_category_id,
                page_number: $page_number
            );

        });

        if (null === $question) {

            return self::handleWhenThereIsNoQuestion(
                sub_category_id: $sub_category_id,
                page_number: $page_number
            );

        }

        if ($page_number > 1) {
            self::logQuestionResult($sub_category_id);
        }

        self::logQuestionHistory(
            sub_category_id: $sub_category_id,
            question_id: $question->id,
            page_number: $page_number
        );


        return self::formatQuestionBody(
            question: $question,
            sub_category_id: $sub_category_id,
            page_number: $page_number
        );
    }

    public static function handleWrongAnswer(object $message): array
    {
        $result = currentTelegramUser()
            ->results()
            ->where('sub_category_id', $message->s)
            ->first();

        if (null !== $result) {

            $result->update([
                'total_correct_answers' => $message->p - 1 === 1 ? 0 : $result->total_correct_answers,
                'total_incorrect_answers' => $message->p - 1 === 1 ? 1 : $result->total_incorrect_answers + 1,
            ]);
        }

        return self::get(
            sub_category_id: $message->s,
            page_number: $message->p,
        );
    }

    private static function logQuestionResult(int $sub_category_id): void
    {
        $result = currentTelegramUser()
            ->results()
            ->where('sub_category_id', $sub_category_id)
            ->first();

        if (null !== $result) {

            $result->update([
                'total_correct_answers' => $result->total_correct_answers + 1,
            ]);
        }

    }

    private static function logQuestionHistory(int $sub_category_id, int $question_id, int $page_number): void
    {

        defer(callback: function () use ($sub_category_id, $question_id, $page_number): void {

            QuestionHistoryMiddleware::handle(
                user: currentTelegramUser(),
                question_id: $question_id,
                sub_category_id: $sub_category_id,
                page_number: $page_number === 1 ? null : $page_number
            );

        });

    }
    private static function handleWhenThereIsNoQuestion(int $sub_category_id, int $page_number): array
    {
        defer(callback: function () use ($sub_category_id): void {

            QuestionHistoryMiddleware::handle(
                user: currentTelegramUser(),
                question_id: null,
                sub_category_id: $sub_category_id,
                page_number: null
            );
        });

        $question = self::getQuestionBySubCategoryId(
            sub_category_id: $sub_category_id,
            page_number: $page_number - 1
        );

        $callback_data = self::getCallbackData(
            model: SubCategory::class,
            id: $question->subCategory->category_id
        );

        $menu = self::makeInlineKeyboard()->row(self::getBackHomeButtons($callback_data));

        return [
            'type' => 'message',
            'reply_markup' => $menu,
            'parse_mode' => 'HTML',
            'text' => "🏁 Testlar Tugadi 🏁",
            'answerCallbackText' => '🏁 Testlar Tugadi 🏁',
        ];
    }
    protected static function formatQuestionBody(Question $question, int $sub_category_id, int $page_number): array
    {
        $callback_data = [
            's' => $sub_category_id, // sub_category_id
            'p' => $page_number + 1 //  pagination number
        ];

        $letters = ['a', 'b', 'c', 'd', 'e', 'f', 'g'];

        $keyboards = [];

        foreach ($question->questionOptions as $key => $option) {

            $callback_data['m'] = $option->is_answer ? 'Q' : 'W';

            $keyboards[] = Keyboard::inlineButton([
                'text' => $letters[$key],
                'callback_data' => json_encode($callback_data),
            ]);
        }

        if (1 === $page_number) {

            $callback_data = [
                'm' => 'S', // subcategory model
                'id' => $question->subCategory->category_id, // sub_category_id
            ];

        } else {
            $callback_data = [
                'm' => 'Q', // question model
                's' => $sub_category_id, // sub_category_id
                'p' => $page_number - 1, // pagination number
            ];
        }

        $keyboard = self::makeInlineKeyboard()
            ->row($keyboards)
            ->row([
                Keyboard::inlineButton([
                    'text' => '⬅️ Orqaga',
                    'callback_data' => json_encode($callback_data),
                ])
            ])
            ->row([
                Keyboard::inlineButton([
                    'text' => '🏁 Testni yakunlash',
                    'callback_data' => json_encode(['m' => 'base', 'id' => '']),
                ])
            ]);


        if ($question->file === null) {

            return [
                'type' => 'message',
                'reply_markup' => $keyboard,
                'parse_mode' => 'HTML',
                'text' => self::formatQuestion($question, $page_number),
                'answerCallbackText' => ($page_number === 1) ? "To'g'ri ✅" : '🤞 Omad 🤞'
            ];
        }

        return [
            'type' => 'file',
            'reply_markup' => $keyboard,
            'parse_mode' => 'HTML',
            'file' => asset("/storage/{$question->file}"),
            'caption' => self::formatQuestion($question, $page_number),
            'answerCallbackText' => ($page_number === 1) ? "To'g'ri ✅" : '🤞 Omad 🤞'
        ];


    }
    protected static function getQuestionBySubCategoryId(int $sub_category_id, int $page_number = 1): ?Question
    {
        return
            (Question::where('sub_category_id', $sub_category_id)
                ->active()
                ->with('questionOptions', 'subCategory.category')
                ->orderBy('id')
                ->paginate(1, '*', 'page', $page_number))->first();

    }
}
