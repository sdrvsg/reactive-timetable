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
        $chats = $user->leadership
            ->chats()
            ->whereNot('id', $user->id)
            ->get()
            ->map(fn (Chat $chat) => __('handlers.transfer.chat', ['name' => $chat->identifier, 'id' => $chat->chat_id]))
            ->implode("\n");

        $bot->sendImagedMessage(__('handlers.transfer.start', ['chats' => $chats]));
        $this->next('chat');
    }

    public function chat(Nutgram $bot): void
    {
        $id = $bot->message()->text;
        $chat = Chat::query()->where('chat_id', $id)->orWhere('username', $id)->first();

        $user = Auth::user();
        $group = $user->leadership;

        if (!$chat || $chat->group->isNot($group) || $chat->is($user)) {

            $bot->sendImagedMessage(__('handler.transfer.error'));
            return;

        }

        $group->leader()->associate($chat)->save();
        $bot->sendImagedMessage(__('handlers.transfer.success'));
        $this->end();
    }
}
