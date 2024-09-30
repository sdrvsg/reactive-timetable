<?php

namespace App\Telegram\Conversations;

use App\Models\Day;
use App\Models\Pair;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;

class UpdateDay extends ImagedEditableInlineMenu
{
    public function start(Nutgram $bot, Day $day): void
    {
        $chat = Auth::user();
        if ($chat->cannot('update', $day))
            return;

        $pairs = $day->pairs->unique('number');
        $buttons = $pairs->map(fn (Pair $pair) => InlineKeyboardButton::make($pair->number, callback_data: "$day->id.$pair->number@subpair"));

        $this->menuText(__('handlers.day.start', ['timetable' => $day->text]))
            ->clearButtons()
            ->addButtonRow(... $buttons->toArray())
            ->addButtonRow(
                InlineKeyboardButton::make(__('handlers.buttons.back'), callback_data: "{$day->date->format('d.m.Y')}@timetable"),
                InlineKeyboardButton::make(__('handlers.buttons.add'), callback_data: "$day->id@add"),
            )
            ->showMenu();
    }

    public function subpair(Nutgram $bot): void
    {
        [$day, $number] = explode('.', $bot->callbackQuery()->data);
        $day = Day::find(intval($day));

        $chat = Auth::user();
        if ($chat->cannot('update', $day))
            return;

        $pairs = $day->pairs()->where('number', intval($number))->get();
        if ($pairs->count() < 2) {

            $this->end();
            UpdatePair::begin($bot, data: ['pair' => $pairs->first()]);
            return;

        }

        $buttons = $pairs->map(fn (Pair $pair) => InlineKeyboardButton::make($pair->name, callback_data: "$pair->id@pair"));
        $this->menuText(__('handlers.day.start', ['timetable' => $day->text]))
            ->clearButtons()
            ->addButtonRow(... $buttons->toArray())
            ->addButtonRow(InlineKeyboardButton::make(__('handlers.buttons.back'), callback_data: "{$day->date->format('d.m.Y')}@timetable"))
            ->showMenu();
    }

    public function add(Nutgram $bot): void
    {
        @[$day_id, $number] = explode('.', $bot->callbackQuery()->data);
        $day = Day::find(intval($day_id));

        $chat = Auth::user();
        if ($chat->cannot('update', $day))
            return;

        if ($number && in_array(intval($number), range(1, 8))) {

            $pair = Pair::create(['number' => intval($number), 'day_id' => $day->id]);
            $this->end();

            UpdatePair::begin($bot, data: ['pair' => $pair]);
            return;

        }

        $buttons = array_map(fn (int $number) => InlineKeyboardButton::make($number, callback_data: "$day->id.$number@add"), range(1, 8));
        $this->menuText(__('handlers.day.start', ['timetable' => $day->text]))
            ->clearButtons()
            ->addButtonRow(... $buttons)
            ->addButtonRow(InlineKeyboardButton::make(__('handlers.buttons.back'), callback_data: "{$day->date->format('d.m.Y')}@timetable"))
            ->showMenu();
    }

    public function timetable(Nutgram $bot): void
    {
        $this->end();
        Timetable::begin($bot, data: ['date' => Carbon::parse($bot->callbackQuery()->data)]);
    }

    public function pair(Nutgram $bot): void
    {
        $this->end();
        UpdatePair::begin($bot, data: ['pair' => Pair::find(intval($bot->callbackQuery()->data))]);
    }
}
