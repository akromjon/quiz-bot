<?php

namespace App\Console\Commands;

use App\Models\Enums\TelegramUserTariffEnum;
use App\Models\TelegramUser;
use App\Telegram\Menu\Menu;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class CheckUserTariffCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-user-tariff-command';

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
        $paid_users = TelegramUser::where('tariff', TelegramUserTariffEnum::PAID)->get();

        $this->info('Checking users...');

        foreach ($paid_users as $user) {

            $this->info('Checking user: ' . $user->user_id);

            if (Carbon::parse($user->next_payment_date === '-' ? '' : $user->next_payment_date)->format('d.m.Y H') === now()->format('d.m.Y H')) {

                $this->info('User: ' . $user->user_id . ' needs to pay!');

                if ($user->balance >= setting('tariff_amount')) {

                    $this->info('User: ' . $user->user_id . ' paid!');

                    $user->balance -= setting('tariff_amount');

                    $user->tariff = TelegramUserTariffEnum::PAID;

                    $user->last_payment_date = now();

                    $user->next_payment_date = now()->addMonth();

                    $user->save();

                    Telegram::sendMessage(Menu::profile($user->user_id));

                    // Telegram::sendMessage(Menu::notifyUserWhenMoneyIsSubtracted($user->user_id, setting('tariff_amount'), $user->balance));
                    Telegram::sendMessage(Menu::notifyUserWhenProfileChanged($user->user_id));


                    $this->info('User: ' . $user->user_id . ' paid and next payment date updated!');

                } elseif ($user->balance < setting('tariff_amount')) {

                    $this->info('User: ' . $user->user_id . ' has not enough balance!');

                    $user->tariff = TelegramUserTariffEnum::FREE;

                    $user->next_payment_date = null;

                    $user->save();

                    Telegram::sendMessage(Menu::profile($user->user_id));

                    Telegram::sendMessage(Menu::notifyUserWhenBalanceIsNotEnough($user->user_id));

                    $this->info('User: ' . $user->user_id . ' tariff changed to FREE!');
                }

            }

        }

        $this->info('Users checked!');
    }
}
