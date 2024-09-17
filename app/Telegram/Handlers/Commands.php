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
            BotCommand::make('start',       __('const.commands.start')),
            BotCommand::make('timetable',   __('const.commands.timetable')),
            BotCommand::make('group',       __('const.commands.group')),
            BotCommand::make('transfer',    __('const.commands.transfer')),
        ];

        $bot->setMyCommands($common, new BotCommandScopeAllPrivateChats);
        foreach (explode(',', config('nutgram.developers')) as $chat_id)
            $bot->setMyCommands([
                ... $common,
                BotCommand::make('commands', __('const.commands.commands')),
            ], new BotCommandScopeChat($chat_id));

        $bot->sendImagedMessage('Done!');
    }
}
