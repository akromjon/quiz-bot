<?php

namespace App\Telegram\Menu;

use App\Models\Category;
use App\Models\Level;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\SubCategory;
use Telegram\Bot\Keyboard\Keyboard;

class Menu
{
    private static function getCallbackData(string $model, string $id): array
    {
        return [
            'm' => class_basename($model)[0],
            'id' => $id,
        ];
    }

    private static function getBackHomeButtons(array $callback_data = ['m' => 'base', 'id' => '']): array
    {
        return [
            Keyboard::inlineButton([
                'text' => '🏠 Bosh Sahifa',
                'callback_data' => json_encode(['m' => 'base', 'id' => '']),
            ]),
            Keyboard::inlineButton([
                'text' => '⬅️ Orqaga',
                'callback_data' => json_encode($callback_data),
            ]),
        ];
    }

    public static function base()
    {
        return Keyboard::make()       
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->row([
                Keyboard::button(['text'=>'Bepul Testlar']),
                Keyboard::button('Umumiy Testlar'),
                Keyboard::button('Sinflar'),
            ])
            ->row([
                Keyboard::button('Admin'),
                Keyboard::button('Help'),
            ])
            ->row([
                Keyboard::button('Sinflar'),
            ]);
    }

    public static function category()
    {
        $categories = Category::active()->get();
        
        $callback_data = self::getCallbackData(SubCategory::class, '');

        $category = Keyboard::make()
            ->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true);

        foreach ($categories as $c) {
            $callback_data['id'] = $c->id;
            $category->row([
                Keyboard::inlineButton([
                    'text' => $c->title,
                    'callback_data' => json_encode($callback_data),
                ]),
            ]);
        }

        $category->row(self::getBackHomeButtons());

        return $category;
    }

    public static function subcategory(int $category_id): array
    {
        $subcategories = SubCategory::active()->where('category_id', $category_id)->get();

        $callback_data = [
            'm' => 'Q',
            'c' => $category_id,
        ];

        $subcategory = Keyboard::make()
            ->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true);

        foreach ($subcategories as $c) {

            $callback_data['s'] = $c->id;

            $subcategory->row([
                Keyboard::inlineButton([
                    'text' => $c->title,
                    'callback_data' => json_encode($callback_data),
                ]),
            ]);
        }

        $callback_data = self::getCallbackData(Category::class, $category_id);

        $subcategory->row(self::getBackHomeButtons($callback_data));

        

        return [
            'keyboard'=>$subcategory,
            'answerCallbackText'=>$subcategories?->first()?->category?->title
        ];
    }

   

    protected static function getNextQuestion(int $sub_category_id, int|null $question_id)
    {
        $query = Question::where('sub_category_id', $sub_category_id)
            ->active()
            ->orderBy('id');

        if ($question_id !== null) {

            $query->where('id', '>', $question_id);
        }

        return $query->first();
    }

   

    public static function question(int $category_id, int $sub_category_id, int $question_id = null, bool $load_next = false): array
    { 

        $question = self::getNextQuestion($sub_category_id, $question_id);

        if (!$question) {

            return self::handleWhenThereIsNoQuestion($category_id);
        }

        return self::handleQuestion(question: $question, sub_category_id: $sub_category_id, category_id: $category_id, load_next: $load_next);
    }

    public static function handlePreviousQuestion(int $category_id,int $sub_category_id, int $question_id): array|null
    {
        $question = Question::where('sub_category_id', $sub_category_id)
            ->active()
            ->where('id', '<', $question_id)
            ->orderByDesc('id')
            ->first();


        if (!$question) {

            return null;
        }

        $keyboard=self::handleQuestion($question, $sub_category_id, $category_id, true);

        $keyboard['answerCallbackText']='🔙 Orqaga';

        return $keyboard;
    }

    

    protected static function handleQuestion(Question $question, int $sub_category_id, int $category_id, bool $load_next): array
    {
        $callback_data = [
            's' => $sub_category_id,
            'c' => $category_id,
            'q' => $question->id,
        ];

        $letters = ['a', 'b', 'c', 'd', 'e', 'f', 'g'];

        $keyboards = [];

        foreach ($question->questionOptions as $key => $option) {

            $callback_data['id'] = $option->id;

            $callback_data['m'] = $option->is_answer ? 'Q' : 'W';            

            $keyboards[] = Keyboard::inlineButton([
                'text' => $letters[$key],
                'callback_data' => json_encode($callback_data),
            ]);
        }

        // $callback_data = self::getCallbackData(SubCategory::class,(string)$sub_category_id);   
        
        $callback_data = [
            'm' => 'P',
            'c' => $category_id,
            's' => $sub_category_id,
            'q' => $question->id,
        ];       
       

        $menu = Keyboard::make()
                ->inline()
                ->setResizeKeyboard(true)
                ->setOneTimeKeyboard(true)
                ->row($keyboards)        
                ->row(self::getBackHomeButtons($callback_data));
                

            $text = "<b>{$question->number} - SAVOL:</b>\n\n";
            $text .= "<b>{$question->question}</b>\n\n";

            $text .= implode("\n", $question->questionOptions->pluck('option')->toArray());

            return [
                'type' => $load_next ? 'edit_message' : 'message',
                'reply_markup' => $menu,
                'parse_mode' => 'HTML',
                'text' => $text,
                'answerCallbackText'=>$load_next ? "To'g'ri ✅" : '🤞 Omad 🤞'
            ];
    }    

    protected static function handleWhenThereIsNoQuestion(int $category_id): array
    {
        $callback_data = self::getCallbackData(SubCategory::class, $category_id);

        $menu = Keyboard::make()
            ->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->row(self::getBackHomeButtons($callback_data));

        return [
            'type' => 'message',
            'reply_markup' => $menu,
            'parse_mode' => 'HTML',
            'text' => "🏁 Testlar Tugadi 🏁",
            'answerCallbackText'=>'🏁 Testlar Tugadi 🏁',
        ];
    }
}
