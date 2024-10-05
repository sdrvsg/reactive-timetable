<?php

namespace App\Telegram\Conversations;

use App\Models\Day;
use App\Models\Deadline;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;

class UpdateDeadlines extends ImagedEditableInlineMenu
{
    public function start(Nutgram $bot, Day $day): void
    {
        $chat = Auth::user();
        if ($chat->cannot('update', $day))
            return;

        $buttons = $day->deadlines->map(fn (Deadline $deadline) => InlineKeyboardButton::make($deadline->subject, callback_data: "$deadline->id@deadline"));

        $this->menuText(__('handlers.day.deadlines', ['timetable' => $day->text]))
            ->clearButtons()
            ->addButtonRow(... $buttons->toArray())
            ->addButtonRow(
                InlineKeyboardButton::make(__('handlers.buttons.back'), callback_data: "{$day->date->format('d.m.Y')}@timetable"),
                InlineKeyboardButton::make(__('handlers.buttons.add'), callback_data: "$day->id@add"),
            )
            ->showMenu();
    }

    public function add(Nutgram $bot): void
    {
        $chat = Auth::user();
        $day_id = $bot->callbackQuery()?->data;

        $day_id = intval($bot->isCallbackQuery() ? $day_id : $bot->getUserData('day'));
        $day = Day::find($day_id);
        $bot->deleteUserData('day');

        if (!$day || $chat->cannot('update', $day))
            return;

        if (!$bot->message()?->from->is_bot && ($subject = $bot->message()?->text)) {

            $deadline = Deadline::create(['subject' => $subject, 'day_id' => $day->id]);
            $this->end();

            UpdateDeadline::begin($bot, data: ['deadline' => $deadline]);
            return;

        }

        $bot->setUserData('day', $day->id);
        $this->menuText(__('handlers.day.deadline', ['timetable' => $day->text]))
            ->clearButtons()
            ->addButtonRow(InlineKeyboardButton::make(__('handlers.buttons.back'), callback_data: "{$day->date->format('d.m.Y')}@timetable"))
            ->orNext('add')
            ->showMenu();
    }

    public function timetable(Nutgram $bot): void
    {
        $this->end();
        Timetable::begin($bot, data: ['date' => Carbon::parse($bot->callbackQuery()->data)]);
    }

    public function deadline(Nutgram $bot): void
    {
        $this->end();
        UpdateDeadline::begin($bot, data: ['deadline' => Deadline::find(intval($bot->callbackQuery()->data))]);
    }
}
