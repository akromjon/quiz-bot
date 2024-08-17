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
    private static function get_callback_data(string $model, string $id): array
    {
        return [
            'm' => class_basename($model)[0],
            'id' => $id,
        ];
    }

    private static function get_back_home_buttons(array $callback_data = ['m' => 'base', 'id' => '']): array
    {
        return [
            Keyboard::inlineButton([
                'text' => '🏠 Bosh Sahifa',
                'callback_data' => json_encode(['m' => 'base', 'id' => ''])
            ]),
            Keyboard::inlineButton([
                'text' => '⬅️ Orqaga',
                'callback_data' => json_encode($callback_data)
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
                Keyboard::button('Sinflar')
            ])
            ->row([
                Keyboard::button('Admin'),
                Keyboard::button('Help')
            ])
            ->row([
                Keyboard::button('Tarriflar')
            ]);
    }

    public static function category()
    {
        $category = Keyboard::make()
            ->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true);

        $categories = Category::active()->get();

        $callback_data = self::get_callback_data(SubCategory::class, '');

        foreach ($categories as $c) {

            $callback_data['id'] = $c->id;

            $category->row([Keyboard::inlineButton(
                [
                    'text' => $c->title,
                    'callback_data' => json_encode($callback_data)
                ]
            )]);
        }

        $category->row(self::get_back_home_buttons());

        return $category;
    }

    public static function subcategory(string $category_id)
    {
        $subcategory = Keyboard::make()
            ->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true);

        $subcategories = SubCategory::active()->where('category_id', $category_id)->get();       

        // Q = Question::class

        $callback_data = ['m'=>'Q'];

        $callback_data['c_id'] =$category_id;

        foreach ($subcategories as $c) {

            $callback_data['sc_id'] = $c->id;

            $subcategory->row([Keyboard::inlineButton(
                [
                    'text' => $c->title,
                    'callback_data' => json_encode($callback_data)
                ]
            )]);
        }

        $callback_data = self::get_callback_data(Category::class, $category_id);

        $subcategory->row(self::get_back_home_buttons($callback_data));

        return $subcategory;
    }

    public static function question(string $category_id,string $sub_category_id, string $question_id = null): array
    {
        $menu = Keyboard::make()
            ->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true);

        $question = Question::where('sub_category_id', $sub_category_id);


        if (!is_null($question_id)) {

            $question = $question->where('id', '>', $question_id);
        }

        $question = $question->active()->orderBy('id')->first();

        if(!$question && empty($question)){
            
            $callback_data = self::get_callback_data(SubCategory::class, $category_id);

            $menu->row(self::get_back_home_buttons($callback_data));

            return [
                'type' => 'message',
                'reply_markup' => $menu,
                'parse_mode' => 'HTML',
                'text' => "Testlar Tugadi"
            ];
        }        

        // Q=Question::class

        $callback_data = ['m'=>'Q'];

        $callback_data['sc_id'] = $sub_category_id;

        $callback_data['c_id'] = $category_id;

        $callback_data['q_id'] = $question->id;

        $letters = ['a', 'b', 'c', 'd', 'e', 'f', 'g'];

        foreach ($question->questionOptions as $key => $option) {

            $callback_data['id'] = $option->id;

            logger((json_encode($callback_data)));

            $keyboards[] = Keyboard::inlineButton(
                [
                    'text' => $letters[$key],
                    'callback_data' => json_encode($callback_data)
                ]
            );
        }

        $menu->row($keyboards);

        $text = "<b>$question->question</b>\n\n";

        foreach ($question->questionOptions as $option) {
            $text .= $option->option . "\n";
        }

        return [
            'type' => 'message',
            'reply_markup' => $menu,
            'parse_mode' => 'HTML',
            'text' => $text
        ];
    }
}
