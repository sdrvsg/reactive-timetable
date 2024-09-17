<?php

namespace App\Telegram\Conversations;

use App\Models\Chat;
use App\Models\Group;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class Register extends Conversation
{
    public function start(Nutgram $bot): void
    {
        $bot->sendImagedMessage(__('handlers.register.start'));
        $this->next('group');
    }

    public function group(Nutgram $bot): void
    {
        $number = $bot->message()->text;
        if (!preg_match('/^[0-9]{4}-[0-9]{6}D$/', $number)) {

            $bot->sendImagedMessage(__('handlers.register.error'));
            return;

        }

        $group = Group::query()->firstOrCreate(['number' => $number]);
        $chat = Chat::query()->create(['chat_id' => $bot->chatId(), 'username' => $bot->user()->username, 'group_id' => $group->id]);
        if ($group->chats()->count() <= 1) $group->leader()->associate($chat)->save();

        $buttons = match ($bot->getUserData('action')) {
            'group' => InlineKeyboardMarkup::make()->addRow(InlineKeyboardButton::make(__('handlers.register.back'), switch_inline_query: 'timetable today')),
            default => null
        };

        $bot->set('chat', $chat);
        $bot->deleteUserData('action');

        $bot->sendImagedMessage(__('handlers.register.success'), $buttons);
        $this->end();
    }
}
