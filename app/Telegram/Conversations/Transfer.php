<?php

namespace App\Telegram\Conversations;

use App\Models\Chat;
use Illuminate\Support\Facades\Auth;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;

class Transfer extends Conversation
{
    public function start(Nutgram $bot): void
    {
        $user = Auth::user();
        $chats = $user->leadership->chats()->whereNot('id', $user->id)->get()->map(fn (Chat $chat) => "<code>$chat->identifier</code>: <a href='tg://user?id={$chat->chat_id}'>Профиль</a>")->implode("\n");
        $bot->sendImagedMessage("<b>👋 Введите ID вашего одногруппника, чтобы передать ему права старосты (юзернейм копируется при нажатии)</b>\n\n$chats");
        $this->next('chat');
    }

    public function chat(Nutgram $bot): void
    {
        $id = $bot->message()->text;
        $chat = Chat::query()->where('chat_id', $id)->orWhere('username', $id)->first();

        $user = Auth::user();
        $group = $user->leadership;

        if (!$chat || $chat->group->isNot($group) || $chat->is($user)) {

            $bot->sendImagedMessage("<b>⛔️ Что-то не то ввели</b>");
            return;

        }

        $group->leader()->associate($chat)->save();
        $bot->sendImagedMessage("<b>Отлично!</b>\n\nВы больше не староста 🎉🎉🎉");
        $this->end();
    }
}
