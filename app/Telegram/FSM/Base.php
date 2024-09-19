<?php

namespace App\Telegram\FSM;

use App\Telegram\Menu\Menu;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Collection;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Objects\Update;
use Telegram\Bot\Objects\Message;

abstract class Base
{
    protected Collection $file;
    protected string $chat_id;
    protected int|null $message_id;
    protected string|null|object $message;
    protected Update $update;
    abstract protected function route(): void;

    public static function handle(...$arg): self
    {
        $instance = new static(...$arg);

        $instance->run();

        return $instance;
    }

    public function run(): void
    {
        $this->route();
    }
    protected function __construct(protected string $message_type)
    {
        $this->update = getWebhookUpdate();

        match ($this->message_type) {
            'callback_query' => $this->handleCallbackQuery(),
            'message' => $this->handleMessage(),
            'file' => $this->handleFile(),
            'command' => $this->handleCommand(),
            default => null,
        };
    }

    protected function handleCommand()
    {
        $this->message = $this->update->getMessage()->getText();

        $this->chat_id = $this->update->getMessage()->getChat()->getId();
    }

    protected function handleCallbackQuery(): void
    {
        $this->message = json_decode($this->update->getCallbackQuery()->getData());

        if (($this->message === null) || (is_object($this->message) && !property_exists($this->message, 'm'))) {
            $this->sendMessage(Menu::category());
            return;
        }

        $this->chat_id = $this->update->getCallbackQuery()->getMessage()->getChat()->getId();

        $this->message_id = $this->update->getCallbackQuery()->getMessage()->message_id;


    }

    protected function handleMessage(): void
    {
        $this->message = $this->update->getMessage()->getText();

        $this->chat_id = $this->update->getMessage()->getChat()->getId();

        $this->message_id = $this->update->getMessage()->message_id;
    }

    protected function handleFile(): void
    {
        $message = $this->update->getMessage();

        if ($message->has('photo')) {

            $this->file = $message->getPhoto();

        } elseif ($message->has('document')) {

            $this->file = $message->getDocument();
        }

        $this->chat_id = $this->update->getMessage()->getChat()->getId();
    }

    protected function sendMessage(array $params): void
    {
        $params['chat_id'] = $this->chat_id;

        Telegram::sendMessage($params);
    }

    protected function answerCallbackQuery(array $params): void
    {
        $params['callback_query_id'] = $this->update->getCallbackQuery()->getId();

        Telegram::answerCallbackQuery($params);
    }

    protected function deleteMessage(array $params): void
    {
        $params['chat_id'] = $this->chat_id;

        Telegram::deleteMessage($params);
    }

    protected function sendVideo(array $params): void
    {
        $params['chat_id'] = $this->chat_id;

        $params['video'] = \Telegram\Bot\FileUpload\InputFile::create($params['file']);


        Telegram::sendVideo($params);
    }

    protected function sendPhoto(array $params): void
    {
        $params['chat_id'] = $this->chat_id;

        $params['photo'] = \Telegram\Bot\FileUpload\InputFile::create($params['file']);

        Telegram::sendPhoto($params);
    }

    protected function sendMessageOrFile(array $menu): void
    {
        match ($menu['type']) {
            'file' => $this->sendVideoOrPhoto($menu),
            default => $this->sendMessage($menu),
        };
    }

    protected function sendVideoOrPhoto(array $menu): void
    {
        match (checkFileType($menu['file'])) {
            'photo' => $this->sendPhoto($menu),
            'video' => $this->sendVideo($menu),
            default => $this->sendMessage($menu),
        };
    }

    protected function sendInvoice(array $params): void
    {
        $params['chat_id'] = $this->chat_id;

        Telegram::sendInvoice($params);
    }






}
