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

        $pairs = $day->pairs;
        $buttons = $pairs->map(fn (Pair $pair) => InlineKeyboardButton::make($pair->number, callback_data: "$pair->id@pair"));

        $this->menuText(__('handlers.day.start', ['timetable' => $day->text]))
            ->clearButtons()
            ->addButtonRow(... $buttons->toArray())
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
