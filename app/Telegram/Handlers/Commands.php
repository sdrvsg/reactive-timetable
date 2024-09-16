<?php

namespace App\Telegram\Handlers;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Command\BotCommand;
use SergiX44\Nutgram\Telegram\Types\Command\BotCommandScopeAllPrivateChats;
use SergiX44\Nutgram\Telegram\Types\Command\BotCommandScopeChat;

class Commands
{
    public function __invoke(Nutgram $bot, ?string $param = null): void
    {
        $common = [
            BotCommand::make('start', 'Информация'),
            BotCommand::make('timetable', 'Узнать нормальное расписание'),
            BotCommand::make('group', 'Сменить группу'),
            BotCommand::make('transfer', 'Передать старосту'),
        ];
        $bot->setMyCommands($common, new BotCommandScopeAllPrivateChats);

        foreach (explode(',', env('TELEGRAM_DEVELOPERS')) as $chat_id)
            $bot->setMyCommands([
                ... $common,
                BotCommand::make('commands', 'Обновить команды'),
            ], new BotCommandScopeChat($chat_id));

        $bot->sendImagedMessage('Готово');
    }
}
