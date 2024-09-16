<?php

namespace App\Telegram\Middleware;

use App\Models\Chat;
use Illuminate\Support\Facades\Auth;
use SergiX44\Nutgram\Nutgram;

class RetrieveAccount
{
    public function __invoke(Nutgram $bot, $next): void
    {
        $chat_id = $bot->chatId() ?? $bot->callbackQuery()?->message->chat->id ?? $bot->inlineQuery()?->from->id;
        $chat = Chat::query()->where('chat_id', $chat_id)->first();

        if ($chat) Auth::login($chat);
        $next($bot);
    }
}
