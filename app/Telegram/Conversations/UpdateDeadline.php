<?php

namespace App\Telegram\Conversations;

use App\Models\Day;
use App\Models\Deadline;
use Illuminate\Support\Facades\Auth;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;

class UpdateDeadline extends ImagedEditableInlineMenu
{
    public function start(Nutgram $bot, Deadline $deadline): void
    {
        $chat = Auth::user();
        if ($chat->cannot('update', $deadline->day))
            return;

        $this->menuText(__('handlers.deadline.start', ['timetable' => $deadline->text]))
            ->clearButtons()
            ->addButtonRow(
                InlineKeyboardButton::make(__('handlers.buttons.subject'), callback_data: "$deadline->id@subject"),
                InlineKeyboardButton::make(__('handlers.buttons.description'), callback_data: "$deadline->id@description"),
            )
            ->addButtonRow(
                InlineKeyboardButton::make(__('handlers.buttons.delete'), callback_data: "$deadline->id@delete"),
                InlineKeyboardButton::make(__('handlers.buttons.back'), callback_data: "{$deadline->day->id}@day"),
            )
            ->showMenu();
    }

    public function day(Nutgram $bot): void
    {
        $this->end();
        UpdateDeadlines::begin($bot, data: ['day' => Day::find(intval($bot->callbackQuery()->data))]);
    }

    public function update(Nutgram $bot): void
    {
        $this->end();
        self::begin($bot, data: ['deadline' => Deadline::find(intval($bot->callbackQuery()->data))]);
    }

    public function subject(Nutgram $bot): void
    {
        $chat = Auth::user();
        $deadline = Deadline::find(intval($bot->callbackQuery()?->data ?? $bot->getUserData('deadline')));
        $bot->deleteUserData('deadline');

        if (!$deadline || $chat->cannot('update', $deadline->day))
            return;

        if (!$bot->message()->from->is_bot && ($subject = $bot->message()->text)) {

            $deadline->subject = $subject;
            $deadline->save();

            $this->end();
            self::begin($bot, data: ['deadline' => $deadline]);
            return;

        }

        $bot->setUserData('deadline', $deadline->id);
        $this->menuText(__('handlers.deadline.subject', ['timetable' => $deadline->text, 'value' => $deadline->subject]))
            ->clearButtons()
            ->addButtonRow(InlineKeyboardButton::make(__('handlers.buttons.back'), callback_data: "$deadline->id@update"))
            ->orNext('subject')
            ->showMenu();
    }

    public function description(Nutgram $bot): void
    {
        $chat = Auth::user();
        $deadline = Deadline::find(intval($bot->callbackQuery()?->data ?? $bot->getUserData('deadline')));
        $bot->deleteUserData('deadline');

        if (!$deadline || $chat->cannot('update', $deadline->day))
            return;

        if (!$bot->message()->from->is_bot && ($description = $bot->message()->text)) {

            $deadline->description = $description;
            $deadline->save();

            $this->end();
            self::begin($bot, data: ['deadline' => $deadline]);
            return;

        }

        $bot->setUserData('deadline', $deadline->id);
        $this->menuText(__('handlers.deadline.description', ['timetable' => $deadline->text, 'value' => $deadline->description]))
            ->clearButtons()
            ->addButtonRow(InlineKeyboardButton::make(__('handlers.buttons.back'), callback_data: "$deadline->id@update"))
            ->orNext('description')
            ->showMenu();
    }

    public function delete(Nutgram $bot): void
    {
        @[$deadline_id, $agree] = explode('.', $bot->callbackQuery()->data);
        $chat = Auth::user();
        $deadline = Deadline::find(intval($deadline_id));

        if ($chat->cannot('update', $deadline->day))
            return;

        if ($agree === 'yes') {

            $day = $deadline->day;
            $deadline->delete();
            $this->end();

            UpdateDeadlines::begin($bot, data: ['day' => $day]);
            return;

        }

        $this->menuText(__('handlers.deadline.delete', ['timetable' => $deadline->text]))
            ->clearButtons()
            ->addButtonRow(
                InlineKeyboardButton::make(__('handlers.buttons.yes'), callback_data: "$deadline->id.yes@delete"),
                InlineKeyboardButton::make(__('handlers.buttons.back'), callback_data: "$deadline->id@update"),
            )
            ->showMenu();
    }
}
