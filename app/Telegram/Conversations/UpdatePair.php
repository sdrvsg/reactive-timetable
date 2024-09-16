<?php

namespace App\Telegram\Conversations;

use App\Enums\PairType;
use App\Models\Day;
use App\Models\Pair;
use Illuminate\Support\Facades\Auth;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;

class UpdatePair extends ImagedEditableInlineMenu
{
    public function start(Nutgram $bot, Pair $pair): void
    {
        $chat = Auth::user();
        if ($chat->cannot('update', $pair->day))
            return;

        $this->menuText("$pair->text\n\n👉 Что поменять?")
            ->clearButtons()
            ->addButtonRow(
                InlineKeyboardButton::make('Вид занятия', callback_data: "$pair->id@type"),
                InlineKeyboardButton::make('Название', callback_data: "$pair->id@name"),
            )
            ->addButtonRow(
                InlineKeyboardButton::make('Преподаватель', callback_data: "$pair->id@teacher"),
                InlineKeyboardButton::make('Группы', callback_data: "$pair->id@groups"),
            )
            ->addButtonRow(
                InlineKeyboardButton::make('Место проведения', callback_data: "$pair->id@place"),
                InlineKeyboardButton::make('Местами', callback_data: "$pair->id@number"),
            )
            ->addButtonRow(
                InlineKeyboardButton::make($pair->is_present ? 'Удалить' : 'Добавить', callback_data: $pair->is_present ? "$pair->id@delete" : "$pair->id@add"),
                InlineKeyboardButton::make('Назад', callback_data: "{$pair->day->id}@day"),
            )
            ->showMenu();
    }

    public function day(Nutgram $bot): void
    {
        $this->end();
        UpdateDay::begin($bot, data: ['day' => Day::find(intval($bot->callbackQuery()->data))]);
    }

    public function update(Nutgram $bot): void
    {
        $this->end();
        self::begin($bot, data: ['pair' => Pair::find(intval($bot->callbackQuery()->data))]);
    }

    public function number(Nutgram $bot): void
    {
        @[$pair_id, $pair_number] = explode('.', $bot->callbackQuery()->data);
        $chat = Auth::user();
        $pair = Pair::find(intval($pair_id));

        if ($chat->cannot('update', $pair->day))
            return;

        if ($pair_number && in_array(intval($pair_number), range(1, 8))) {

            $other = $pair->day->pairs()->where('number', intval($pair_number))->first();
            $other->number = $pair->number;
            $other->save();

            $pair->number = intval($pair_number);
            $pair->save();

            $this->end();
            self::begin($bot, data: ['pair' => $pair]);
            return;

        }

        $buttons = array_map(fn (int $number) => InlineKeyboardButton::make(strval($number), callback_data: "$pair->id.$number@number"), array_filter(range(1, 8), fn (int $number) => $number !== $pair->number));
        $this->menuText("{$pair->day->text}\n\n👉 С какой парой поменять местами?")
            ->clearButtons()
            ->addButtonRow(... $buttons)
            ->addButtonRow(InlineKeyboardButton::make('Назад', callback_data: "$pair->id@update"))
            ->showMenu();
    }

    public function type(Nutgram $bot): void
    {
        @[$pair_id, $pair_type] = explode('.', $bot->callbackQuery()->data);
        $chat = Auth::user();
        $pair = Pair::find(intval($pair_id));

        if ($chat->cannot('update', $pair->day))
            return;

        if ($pair_type && in_array($pair_type, PairType::all())) {

            $pair->type = $pair_type;
            $pair->save();

            $this->end();
            self::begin($bot, data: ['pair' => $pair]);
            return;

        }

        $buttons = array_map(fn (PairType $type) => InlineKeyboardButton::make(($pair->type === $type ? '• ' : '') . $type->verbose(), callback_data: "$pair->id.$type->value@type"), PairType::cases());
        $this->menuText("$pair->text\n\n👉 Укажи вид занятия")
            ->clearButtons()
            ->addButtonRow(... $buttons)
            ->addButtonRow(InlineKeyboardButton::make('Назад', callback_data: "$pair->id@update"))
            ->showMenu();
    }

    public function name(Nutgram $bot): void
    {
        $chat = Auth::user();
        $pair = Pair::find(intval($bot->callbackQuery()?->data ?? $bot->getUserData('pair')));
        $bot->deleteUserData('pair');

        if (!$pair || $chat->cannot('update', $pair->day))
            return;

        if (!$bot->message()->from->is_bot && ($name = $bot->message()->text)) {

            $pair->name = $name;
            $pair->save();

            $this->end();
            self::begin($bot, data: ['pair' => $pair]);
            return;

        }

        $bot->setUserData('pair', $pair->id);
        $this->menuText("$pair->text\n\n👉 Введи название пары\nТекущее название: <code>$pair->name</code>")
            ->clearButtons()
            ->addButtonRow(InlineKeyboardButton::make('Назад', callback_data: "$pair->id@update"))
            ->orNext('name')
            ->showMenu();
    }

    public function teacher(Nutgram $bot): void
    {
        $chat = Auth::user();
        $pair = Pair::find(intval($bot->callbackQuery()?->data ?? $bot->getUserData('pair')));
        $bot->deleteUserData('pair');

        if (!$pair || $chat->cannot('update', $pair->day))
            return;

        if (!$bot->message()->from->is_bot && ($teacher = $bot->message()->text)) {

            $pair->teacher = $teacher;
            $pair->save();

            $this->end();
            self::begin($bot, data: ['pair' => $pair]);
            return;

        }

        $bot->setUserData('pair', $pair->id);
        $this->menuText("$pair->text\n\n👉 Введи преподавателя\nСейчас: <code>$pair->teacher</code>")
            ->clearButtons()
            ->addButtonRow(InlineKeyboardButton::make('Назад', callback_data: "$pair->id@update"))
            ->orNext('teacher')
            ->showMenu();
    }

    public function place(Nutgram $bot): void
    {
        $chat = Auth::user();
        $pair = Pair::find(intval($bot->callbackQuery()?->data ?? $bot->getUserData('pair')));
        $bot->deleteUserData('pair');

        if (!$pair || $chat->cannot('update', $pair->day))
            return;

        if (!$bot->message()->from->is_bot && ($place = $bot->message()->text)) {

            $pair->place = $place;
            $pair->save();

            $this->end();
            self::begin($bot, data: ['pair' => $pair]);
            return;

        }

        $bot->setUserData('pair', $pair->id);
        $this->menuText("$pair->text\n\n👉 Введи место проведения\nСейчас: <code>$pair->place</code>")
            ->clearButtons()
            ->addButtonRow(InlineKeyboardButton::make('Назад', callback_data: "$pair->id@update"))
            ->orNext('place')
            ->showMenu();
    }

    public function groups(Nutgram $bot): void
    {
        $chat = Auth::user();
        $pair = Pair::find(intval($bot->callbackQuery()?->data ?? $bot->getUserData('pair')));
        $bot->deleteUserData('pair');

        if (!$pair || $chat->cannot('update', $pair->day))
            return;

        if (!$bot->message()->from->is_bot && ($groups = $bot->message()->text)) {

            $pair->groups = $groups;
            $pair->save();

            $this->end();
            self::begin($bot, data: ['pair' => $pair]);
            return;

        }

        $bot->setUserData('pair', $pair->id);
        $this->menuText("$pair->text\n\n👉 Введи группы / подгруппы\nСейчас: <code>$pair->groups</code>")
            ->clearButtons()
            ->addButtonRow(InlineKeyboardButton::make('Назад', callback_data: "$pair->id@update"))
            ->orNext('groups')
            ->showMenu();
    }

    public function add(Nutgram $bot): void
    {
        $chat = Auth::user();
        $pair = Pair::find(intval($bot->callbackQuery()->data));

        if ($chat->cannot('update', $pair->day))
            return;

        $pair->is_present = true;
        $pair->save();

        $this->end();
        UpdateDay::begin($bot, data: ['day' => $pair->day]);
    }

    public function delete(Nutgram $bot): void
    {
        @[$pair_id, $agree] = explode('.', $bot->callbackQuery()->data);
        $chat = Auth::user();
        $pair = Pair::find(intval($pair_id));

        if ($chat->cannot('update', $pair->day))
            return;

        if ($agree === 'yes') {

            $pair->is_present = false;
            $pair->type = null;
            $pair->name = null;
            $pair->place = null;
            $pair->teacher = null;
            $pair->groups = null;
            $pair->save();

            $this->end();
            UpdateDay::begin($bot, data: ['day' => $pair->day]);
            return;

        }

        $this->menuText("$pair->text\n\n👉 Точно отменить пару?")
            ->clearButtons()
            ->addButtonRow(
                InlineKeyboardButton::make('Да', callback_data: "$pair->id.yes@delete"),
                InlineKeyboardButton::make('Назад', callback_data: "$pair->id@update"),
            )
            ->showMenu();
    }
}
