<?php

namespace App\Console\Commands;

use App\Models\Enums\TelegramUserTariffEnum;
use App\Models\TelegramUser;
use App\Telegram\Menu\Menu;
use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class UserTrialGiverCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:user-trial-giver-command {user_id}';

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
        $user = TelegramUser::where('user_id', $this->argument('user_id'))
            ->where('tariff', TelegramUserTariffEnum::FREE)
            ->first();

        $trial_day = (int)setting('trial_day') ?? 1;

        if (empty($user)) {
            return;
        }

        $user->tariff = TelegramUserTariffEnum::PAID;

        $user->last_payment_date = now();

        $user->next_payment_date = now()->addDays($trial_day);

        $user->save();

        Telegram::sendMessage(Menu::profile($user->user_id));

        Telegram::sendMessage(Menu::notifyUserWhenProfileChanged($user->user_id,$trial_day));

    }
}
