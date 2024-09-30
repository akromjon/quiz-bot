<?php

namespace App\Console\Commands;

use App\Models\Question;
use App\Models\TelegramUserNotification;
use Illuminate\Console\Command;
use App\Notifications\TelegramUserNotification as AliasedTelegramUserNotification;
use Illuminate\Support\Facades\Cache;

class UpdateFreeQuizDailyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-free-quiz-daily-command';

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
        $this->info('starting UpdateFreeQuizDailyCommand');

        $question_limit = setting('free_question_limit') ?? 30;

        for ($i = 1; $i <= $question_limit; $i++) {
            Cache::forget("free_question:{$i}");
        }

        Question::where('is_free', true)->update(['is_free' => false]);

        $questions = Question::inRandomOrder()->limit($question_limit)->get();

        foreach ($questions as $q) {
            $q->update(['is_free' => true]);
        }

        $notification = TelegramUserNotification::whereJsonContains('params->name', 'update_free_quiz_notification')
            ->first();

        if (!empty($notification)) {

            $this->info('sending notifications to free users');

            $notification->notify(new AliasedTelegramUserNotification($notification));
        }

        $this->info('ended UpdateFreeQuizDailyCommand');

    }
}
