<?php

namespace App\Telegram\FSM;

use App\Telegram\Menu\Menu;

class CallbackQueryFSM extends Base
{
    protected object $update;    
    protected object $callback_query;
    protected string $message;
    protected int $message_id;
    public static function handle(...$params): self
    {
        $instance = new self(...$params);        

        $instance->run();

        return $instance;
    }

    public function run():void{
        
        $this->callback_query = $this->update->getCallbackQuery();
        
        $this->message = $this->update->getCallbackQuery()->getData();
        
        $this->chat_id = $this->update->getCallbackQuery()->getMessage()->getChat()->getId();

        $this->message_id=$this->callback_query->getMessage()->message_id;
        
        $this->route();
    }
    protected function route(): void
    {
        $message = json_decode($this->message);

        match ($message->m) {
            'base' => $this->handleBase(),
            'C' => $this->editMessageText($this->createEditMessage($this->message_id,'Sinflar: ', Menu::category())),
            'S' => $this->editMessageText($this->createEditMessage($this->message_id,"Bo'limlar:", Menu::subcategory($message->id))),
            'Q' => $this->handleQuestion($message),
            'W' => $this->handleWrongAnswer(),
            default => $this->editMessageText(
                $this->createEditMessage($this->message_id,'Hozirda Bu boyicha ishlamoqdamiz...!')
            ),
        };
    }

    protected function handleBase():void{

        $this->deleteMessage([
            'message_id'=>$this->message_id,
        ]);

        $this->sendMessage($this->createMessage('Asosiy Menu:', Menu::base()));
    }

   

    protected function handleQuestion(object $message): void
    {
        $menu=[];

        if(property_exists($message, 'o')){
            
           $menu= Menu::question(category_id: 0, sub_category_id: 0, question_id: $message->o, load_next: false, can_load_old_question: true);

        }
        else if(property_exists($message, 'q')){
            
            $menu=  Menu::question(category_id: $message->c, sub_category_id:$message->s, question_id:$message->q, load_next: true);
        }
        else{
            
            $menu= Menu::question(category_id: $message->c, sub_category_id: $message->s);
        }
    
        $this->editMessageText(
            $this->createEditMessage($this->message_id, $menu['text'], $menu['reply_markup'],$menu['parse_mode'])
        );
    }

    protected function handleWrongAnswer(): void
    {
        $this->telegram::answerCallbackQuery([
            'callback_query_id' => $this->callback_query->getId(),
            'text' => "Noto'g'ri ❌",
            'show_alert' => true,
        ]);
    }
}
