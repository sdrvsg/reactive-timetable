<?php

namespace App\Telegram\Conversations;

use App\Jobs\SendDayNotification;
use App\Models\Day;
use App\Services\TimetableService;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;

class Timetable extends ImagedEditableInlineMenu
{
    public function start(Nutgram $bot, ?Carbon $date = null, bool $refresh = false): void
    {
        $now = now();
        $date = $date ?? $now;
        $chat = Auth::user();

        $timetableService = App::make(TimetableService::class);
        $day = $timetableService->getTimetable($chat->group, $date, $refresh);

        $edit = [InlineKeyboardButton::make('Сегодня', callback_data: "{$now->format('d.m.Y')}@date")];
        if ($chat->can('update', $day)) {

            $edit[] = InlineKeyboardButton::make('Пост', callback_data: "$day->id@post");
            $edit[] = InlineKeyboardButton::make('Обновить', callback_data: "$day->id@refresh");
            $edit[] = InlineKeyboardButton::make('Изменить', callback_data: "$day->id@update");

        }

        $weekdays = [];
        if ($timetableService->getWeekday($date, 1) > Carbon::parse('02.09.2024'))
            $weekdays[] = InlineKeyboardButton::make('⬅️', callback_data: "{$timetableService->getWeekday($date->copy()->subWeek(), 6)->format('d.m.Y')}@date");

        foreach (range(1, 6) as $number)
            $weekdays[] = InlineKeyboardButton::make($timetableService->getWeekdayName($date, $number), callback_data: "{$timetableService->getWeekday($date, $number)->format('d.m.Y')}@date");

        if ($timetableService->getWeekday($date, 6) < Carbon::parse('01.02.2025'))
            $weekdays[] = InlineKeyboardButton::make('➡️', callback_data: "{$timetableService->getWeekday($date->copy()->addWeek(), 1)->format('d.m.Y')}@date");

        $this->menuText($day->text)
            ->clearButtons()
            ->addButtonRow(... $weekdays)
            ->addButtonRow(... $edit)
            ->showMenu();
    }

    public function date(Nutgram $bot): void
    {
        $date = Carbon::parse($bot->callbackQuery()->data);
        $this->start($bot, $date);
    }

    public function refresh(Nutgram $bot): void
    {
        $this->start($bot, Day::find(intval($bot->callbackQuery()->data))->date, true);
    }

    public function update(Nutgram $bot): void
    {
        $this->end();
        UpdateDay::begin($bot, data: ['day' => Day::find(intval($bot->callbackQuery()->data))]);
    }

    public function post(Nutgram $bot): void
    {
        $chat = Auth::user();
        @[$day_id, $action] = explode('.', $bot->callbackQuery()?->data ?? '');

        $day_id = intval($bot->isCallbackQuery() ? $day_id : $bot->getUserData('day'));
        $day = Day::find($day_id);
        $bot->deleteUserData('day');

        if (!$day || $chat->cannot('update', $day))
            return;

        if ($action === 'post' || !$bot->message()?->from->is_bot && ($comment = $bot->message()?->text)) {

            $day->comment = $comment ?? 'ред.';
            $day->save();

            $this->end();
            self::begin($bot, data: ['date' => $day->date]);

            SendDayNotification::dispatch($day);
            return;

        }

        $bot->setUserData('day', $day->id);
        $this->menuText("$day->text\n\n👉 Все одногруппники получат уведомления\nМожно отправить необязательный комментарий")
            ->clearButtons()
            ->addButtonRow(
                InlineKeyboardButton::make('Отправить', callback_data: "$day->id.post@post"),
                InlineKeyboardButton::make('Назад', callback_data: "{$day->date->format('d.m.Y')}@date"),
            )
            ->orNext('post')
            ->showMenu();
    }
}
