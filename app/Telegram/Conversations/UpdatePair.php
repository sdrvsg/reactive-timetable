<?php

namespace App\Telegram\Conversations;

use App\Enums\PairType;
use App\Models\Day;
use App\Models\Pair;
use App\Models\Teacher;
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

        $this->menuText(__('handlers.pair.start', ['timetable' => $pair->text]))
            ->clearButtons()
            ->addButtonRow(
                InlineKeyboardButton::make(__('handlers.buttons.type'), callback_data: "$pair->id@type"),
                InlineKeyboardButton::make(__('handlers.buttons.name'), callback_data: "$pair->id@name"),
            )
            ->addButtonRow(
                InlineKeyboardButton::make(__('handlers.buttons.teacher'), callback_data: "$pair->id@teacher"),
                InlineKeyboardButton::make(__('handlers.buttons.groups'), callback_data: "$pair->id@groups"),
            )
            ->addButtonRow(
                InlineKeyboardButton::make(__('handlers.buttons.place'), callback_data: "$pair->id@place"),
                InlineKeyboardButton::make(__('handlers.buttons.number'), callback_data: "$pair->id@number"),
            )
            ->addButtonRow(
                InlineKeyboardButton::make(__('handlers.buttons.delete'), callback_data: "$pair->id@delete"),
                InlineKeyboardButton::make(__('handlers.buttons.back'), callback_data: "{$pair->day->id}@day"),
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

            $pair->number = intval($pair_number);
            $pair->save();

            $this->end();
            self::begin($bot, data: ['pair' => $pair]);
            return;

        }

        $buttons = array_map(fn (int $number) => InlineKeyboardButton::make(strval($number), callback_data: "$pair->id.$number@number"), array_filter(range(1, 8), fn (int $number) => $number !== $pair->number));
        $this->menuText(__('handlers.pair.number', ['timetable' => $pair->day->text]))
            ->clearButtons()
            ->addButtonRow(... $buttons)
            ->addButtonRow(InlineKeyboardButton::make(__('handlers.buttons.back'), callback_data: "$pair->id@update"))
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
        $this->menuText(__('handlers.pair.type', ['timetable' => $pair->text]))
            ->clearButtons()
            ->addButtonRow(... $buttons)
            ->addButtonRow(InlineKeyboardButton::make(__('handlers.buttons.back'), callback_data: "$pair->id@update"))
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
        $this->menuText(__('handlers.pair.name', ['timetable' => $pair->text, 'value' => $pair->name]))
            ->clearButtons()
            ->addButtonRow(InlineKeyboardButton::make(__('handlers.buttons.back'), callback_data: "$pair->id@update"))
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

            $pair->teacher()->associate(Teacher::query()->firstOrCreate(['name' => $teacher]));
            $pair->save();

            $this->end();
            self::begin($bot, data: ['pair' => $pair]);
            return;

        }

        $bot->setUserData('pair', $pair->id);
        $this->menuText(__('handlers.pair.teacher', ['timetable' => $pair->text, 'value' => $pair->teacher->name]))
            ->clearButtons()
            ->addButtonRow(InlineKeyboardButton::make(__('handlers.buttons.back'), callback_data: "$pair->id@update"))
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
        $this->menuText(__('handlers.pair.place', ['timetable' => $pair->text, 'value' => $pair->place]))
            ->clearButtons()
            ->addButtonRow(InlineKeyboardButton::make(__('handlers.buttons.back'), callback_data: "$pair->id@update"))
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
        $this->menuText(__('handlers.pair.groups', ['timetable' => $pair->text, 'value' => $pair->groups]))
            ->clearButtons()
            ->addButtonRow(InlineKeyboardButton::make(__('handlers.buttons.back'), callback_data: "$pair->id@update"))
            ->orNext('groups')
            ->showMenu();
    }

    public function add(Nutgram $bot): void
    {
        $chat = Auth::user();
        $pair = Pair::find(intval($bot->callbackQuery()->data));

        if ($chat->cannot('update', $pair->day))
            return;

        // $pair->is_present = true;
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

            $pair->delete();
            $this->end();

            UpdateDay::begin($bot, data: ['day' => $pair->day]);
            return;

        }

        $this->menuText(__('handlers.pair.delete', ['timetable' => $pair->text]))
            ->clearButtons()
            ->addButtonRow(
                InlineKeyboardButton::make(__('handlers.buttons.yes'), callback_data: "$pair->id.yes@delete"),
                InlineKeyboardButton::make(__('handlers.buttons.back'), callback_data: "$pair->id@update"),
            )
            ->showMenu();
    }
}
