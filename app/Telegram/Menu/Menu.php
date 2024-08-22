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
                Keyboard::button('Bepul Testlar'),
                Keyboard::button('Umumiy Testlar'),
                Keyboard::button('Sinflar'),
            ])
            ->row([
                Keyboard::button('Admin'),
                Keyboard::button('Help'),
            ])
            ->row([
                Keyboard::button('Tarriflar'),
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

    public static function subcategory(string $category_id)
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

        return $subcategory;
    }

    protected static function getQuestion(int $question_id){
       return Question::where('id', $question_id)
            ->active()
            ->first();       

    }

   

    public static function question(int $category_id, int $sub_category_id, int $question_id = null, bool $load_next = false,bool $can_load_old_question=false): array
    { 

        $question = $can_load_old_question ? self::getQuestion($question_id)  : self::getNextQuestion($sub_category_id, $question_id);

        if (!$question) {

            return self::handleWhenThereIsNoQuestion($category_id);
        }

        return self::handleQuestion(question: $question, sub_category_id: $sub_category_id, category_id: $category_id, load_next: $load_next,old_question_id: $question_id);
    }

    protected static function handleQuestion(Question $question, int $sub_category_id, int $category_id, bool $load_next,int|null $old_question_id): array
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

        

        $callback_data =[];
        
        if($old_question_id===null){
            $callback_data = self::getCallbackData(SubCategory::class,(string)$sub_category_id);
        }else{
            $callback_data = self::getCallbackData(Question::class, (string)$old_question_id);

        }   

        $callback_data['o']=$old_question_id;
        
        $buttons=[Keyboard::inlineButton([
            'text' => '🏠 Bosh Sahifa',
            'callback_data' => json_encode(['m' => 'base', 'id' => '']),
        ]),
        Keyboard::inlineButton([
            'text' => '⬅️ Orqaga',
            'callback_data' => json_encode($callback_data),
            ]),
        ];

        $menu = Keyboard::make()
                ->inline()
                ->setResizeKeyboard(true)
                ->setOneTimeKeyboard(true)
                ->row($keyboards)        
                ->row($buttons);
                

            $text = "<b>{$question->question}</b>\n\n";

            $text .= implode("\n", $question->questionOptions->pluck('option')->toArray());

            return [
                'type' => $load_next ? 'edit_message' : 'message',
                'reply_markup' => $menu,
                'parse_mode' => 'HTML',
                'text' => $text,
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
            'text' => "Testlar Tugadi",
        ];
    }
}
