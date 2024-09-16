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
        $bot->sendImagedMessage("<b>👋 Привет!</b>\n\nЧтобы начать пользоваться ботом, введи номер своей группы ниже:\n\n<b>Формат:</b> <i>xxxx-xxxxxxD</i>");
        $this->next('group');
    }

    public function group(Nutgram $bot): void
    {
        $number = $bot->message()->text;
        if (!preg_match('/^[0-9]{4}-[0-9]{6}D$/', $number)) {

            $bot->sendImagedMessage("<b>⛔️ Неверный формат!</b>\nНужно: <i>xxxx-xxxxxxD</i>");
            return;

        }

        $group = Group::query()->firstOrCreate(['number' => $number]);
        $chat = Chat::query()->create(['chat_id' => $bot->chatId(), 'username' => $bot->user()->username, 'group_id' => $group->id]);
        if ($group->chats()->count() <= 1) $group->leader()->associate($chat)->save();

        $buttons = match ($bot->getUserData('action')) {
            'group' => InlineKeyboardMarkup::make()->addRow(InlineKeyboardButton::make('Вернуться', switch_inline_query: 'timetable today')),
            default => null
        };

        $bot->set('chat', $chat);
        $bot->deleteUserData('action');

        $bot->sendImagedMessage("<b>Отлично!</b>\n\nТеперь можешь ввести /timetable для получения расписания", $buttons);
        $this->end();
    }
}
