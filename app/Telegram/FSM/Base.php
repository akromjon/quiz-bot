<?php

namespace App\Telegram\FSM;


abstract class Base implements FSMInterface
{
    protected string $chat_id;

    protected string $message;    

    public function __construct(protected object &$telegram, protected object &$update){

    }
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

    protected function deleteMessage(array $params):void{
        
        $params['chat_id'] = $this->chat_id;

        $this->telegram::deleteMessage($params);
    }

    protected function createMessage(string $text, $replyMarkup = null, string $parseMode = 'HTML'): array
    {
        return [
            'text' => $text,
            'reply_markup' => $replyMarkup,
            'parse_mode' => $parseMode,
        ];
    }

    protected function createEditMessage(int $message_id,string $text, $replyMarkup = null, string $parseMode = 'HTML'): array
    {
        return [
            'message_id' => $message_id,
            'text' => $text,
            'reply_markup' => $replyMarkup,
            'parse_mode' => $parseMode,
        ];
    }

    abstract public static function handle(...$params): self;
    abstract protected function route(): void;

}
