<?php

namespace App\Telegram\FSM;


abstract class Base implements FSMInterface
{
    public function __construct(protected object &$telegram, protected string|null &$message, protected string &$chat_id, protected object|null &$callback_query) {}
    protected function sendMessage(array $params): void
    {
        $params['chat_id'] = $this->chat_id;

        $this->telegram::sendMessage($params);
    }

    protected function editMessageText(array $params): void
    {
        $params['chat_id'] = $this->chat_id;

        $this->telegram::editMessageText($params);
    }
}
