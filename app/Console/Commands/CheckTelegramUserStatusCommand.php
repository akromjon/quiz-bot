<?php

namespace App\Console\Commands;

use App\Models\TelegramUser;
use Illuminate\Console\Command;
use Telegram\Bot\Exceptions\TelegramResponseException;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class CheckTelegramUserStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-telegram-user-status-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking Telegram users status');

        TelegramUser::chunk(100, function ($users) {

            foreach ($users as $user) {

                try{

                    $this->info("Checking user $user->user_id");

                    Telegram::sendChatAction([
                        'chat_id' => $user->user_id,
                        'action' => 'typing',
                    ]);

                    $this->info("User $user->user_id is active");

                }catch(TelegramResponseException $e) {

                    if (403 === $e->getCode()) {

                        $this->error("User $user->user_id is blocked the bot");

                        $chat_id = null;

                        $data = $e->getResponse()->getRequest()->getParams();

                        Log::error("data:", $data);

                        if(array_key_exists('multipart', $data)) {
                            foreach ($data['multipart'] as $part) {
                                if ($part['name'] === 'chat_id') {
                                    $chat_id = $part['contents'];
                                    break;
                                }
                            }
                        }

                        if(array_key_exists('form_params', $data)) {
                            $chat_id = $data['form_params']['chat_id'];
                        }

                        if (is_int($chat_id)) {

                            TelegramUser::where('user_id', $chat_id)->update(['status' => 'blocked']);
                        }

                        $this->error("User $chat_id is blocked the bot");


                    }

                    Log::error($e->getMessage());
                }

            }
        });
    }
}
