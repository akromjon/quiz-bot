<?php

namespace App\Telegram\Menu;

use App\Models\Category;
use App\Models\Level;
use App\Models\Question;
use App\Models\SubCategory;
use Telegram\Bot\Keyboard\Keyboard;

class Menu
{
    private static function get_callback_data(string $model, string $id): array
    {
        return [
            'model' => $model,
            'id' => $id,
        ];
    }

    private static function get_back_home_buttons(array $callback_data = ['model' => 'base', 'id' => '']): array
    {
        return [
            Keyboard::inlineButton([
                'text' => '🏠 Bosh Sahifa',
                'callback_data' => json_encode(['model' => 'base', 'id' => ''])
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

        $callback_data = self::get_callback_data(Question::class, '');

        foreach ($subcategories as $c) {

            $callback_data['id'] = $c->id;

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

   
}
