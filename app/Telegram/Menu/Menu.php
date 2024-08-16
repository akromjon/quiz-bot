<?php

namespace App\Telegram\Menu;

class Menu
{
    public static function menus(): array
    {
        return [
            'free_tests' => 'Bepul Testlar',
            'general_tests' => 'Umumiy Testlar',
            'classes' => 'Sinflar',
            'admin' => 'Admin',
            'help' => 'Help',
            'tariffs' => 'Tarriflar',
            'menu'=>'Menu',
            'start'=>'/start'
        ];
    }
    public static function get(string $key): string
    {
        return self::menus()[$key];
    }
}
