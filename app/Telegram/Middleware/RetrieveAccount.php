<?php

namespace App\Telegram\Middleware;

use App\Models\Chat;
use Illuminate\Support\Facades\Auth;
use SergiX44\Nutgram\Nutgram;

class RetrieveAccount
{
    public function __invoke(Nutgram $bot, $next): void
    {
        $chat_id = $bot->chatId() ?? $bot->callbackQuery()?->message->chat->id ?? $bot->inlineQuery()?->from->id ?? $bot->chosenInlineResult()->from->id;
        $chat = Chat::query()->where('chat_id', $chat_id)->first();

        if ($chat) {

            $user = $bot->user() ?? $bot->callbackQuery()->from ?? $bot->inlineQuery()->from ?? $bot->chosenInlineResult()->from;
            Auth::login($chat);

            if (!$chat->name)
                $chat->name = "$user->first_name $user->last_name";

            $chat->was_online_at = now();
            $chat->save();

        }

        $next($bot);
    }
}
