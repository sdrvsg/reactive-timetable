<?php

namespace App\Telegram\Conversations;

use App\Models\Group as Model;
use Illuminate\Support\Facades\Auth;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;

class Group extends Conversation
{
    public function start(Nutgram $bot): void
    {
        $bot->sendImagedMessage("<b>⏭ Чтобы сменить группу, введи ее ниже</b>\n\n<b>Формат:</b> <i>xxxx-xxxxxxD</i>");
        $this->next('group');
    }

    public function group(Nutgram $bot): void
    {
        $number = $bot->message()->text;
        if (!preg_match('/^[0-9]{4}-[0-9]{6}D$/', $number)) {

            $bot->sendImagedMessage("<b>⛔️ Неверный формат!</b>\nНужно: <i>xxxx-xxxxxxD</i>");
            return;

        }

        $group = Model::query()->firstOrCreate(['number' => $number]);
        $user = Auth::user();
        $old_group = $user->group;

        if ($group->is($old_group)) {

            $bot->sendImagedMessage("<b>⛔️ Да ты пидор</b>");
            return;

        }

        $user->group()->associate($group);
        $user->save();
        if ($group->chats()->count() <= 1) $group->leader()->associate($user)->save();

        $amount = mt_rand(100, 1000000);
        $bot->sendImagedMessage("<b>Отлично!</b>\n\nГруппа <b>$group->number</b> покупает вас у группы <b>$old_group->number</b> за <i>$$amount</i>");
        $this->end();
    }
}
