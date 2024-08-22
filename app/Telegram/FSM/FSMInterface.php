<?php

namespace App\Telegram\FSM;


interface FSMInterface
{
    public function route(): void;
}
