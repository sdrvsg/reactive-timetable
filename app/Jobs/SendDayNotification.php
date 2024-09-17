<?php

namespace App\Jobs;

use App\Models\Day;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Nutgram;

class SendDayNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 60 * 60 * 24;

    public function __construct(public Day $day) {}

    public function handle(Nutgram $bot): void
    {
        ini_set('max_execution_time', '0');
        foreach ($this->day->group->chats as $chat) {

            if ($chat->is_blocked)
                continue;

            try {

                $bot->sendImagedMessage(
                    text: $this->day->text,
                    options: ['chat_id' => $chat->chat_id]
                );

            } catch (\Throwable $e) {

                if (str_contains($e->getMessage(), 'bot was blocked') || str_contains($e->getMessage(), 'bot was kicked') || str_contains($e->getMessage(), 'chat not found')) {

                    $chat->is_blocked = true;
                    $chat->save();

                } else Log::error($e->getMessage());

            }

            sleep(0.7);

        }
    }
}
