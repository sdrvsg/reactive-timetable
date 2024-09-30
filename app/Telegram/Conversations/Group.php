<?php

namespace App\Telegram\Conversations;

use App\Models\Chat;
use App\Models\Group as Model;
use Illuminate\Support\Facades\Auth;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;

class Group extends Conversation
{
    public function start(Nutgram $bot): void
    {
        $bot->sendImagedMessage(__('handlers.group.start'));
        $this->next('group');
    }

    public function group(Nutgram $bot): void
    {
        $number = $bot->message()->text;
        if (!preg_match('/^[0-9]{4}-[0-9]{6}D$/', $number)) {

            $bot->sendImagedMessage(__('handlers.group.error'));
            return;

        }

        /** @var Chat $user */
        $user = Auth::user();

        $group = Model::query()->firstOrCreate(['number' => $number]);
        $old_group = $user->group;

        if ($group->is($old_group)) {

            $bot->sendImagedMessage(__('handlers.group.gay'));
            return;

        }

        $user->group()->associate($group);
        $user->save();
        if ($group->chats()->count() <= 1) $group->leader()->associate($user)->save();

        $this->end();
        $bot->sendImagedMessage(__('handlers.group.success', [
            'new' => $group->number,
            'old' => $old_group->number,
            'amount' => mt_rand(100, 1000000),
        ]));
    }
}
