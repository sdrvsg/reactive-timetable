<?php

namespace App\Telegram\Handlers;

use App\Telegram\Conversations\Register;
use Illuminate\Support\Facades\Auth;
use SergiX44\Nutgram\Nutgram;

class Help
{
    public function __invoke(Nutgram $bot, ?string $param = null): void
    {
        $bot->setUserData('action', $action = trim($param));
        if ($action === 'group' && !Auth::check()) {

            Register::begin($bot);
            return;

        }

        $bot->asResponse()->sendImagedMessage("<b>Реактивное расписание? Да ну</b>\n\nКороче меньше пиздежа, команды такие:\n/start — Данное окошко\n/timetable — Расписание\n/group — Сменить группу\n/transfer — Передать старосту\n\n<b>Для групп только <i>Инлайн</i> режим:</b>\n\n<code>@ssauReactiveBot {сегодня, завтра, 11 сен, 15.12.2024...}</code>\n<i>Скинуть расписание указанной даты</i>");
    }
}
