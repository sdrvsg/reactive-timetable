<?php
/** @var SergiX44\Nutgram\Nutgram $bot */

use App\Telegram\Commands\Timetable;
use App\Telegram\Conversations\Group;
use App\Telegram\Conversations\Transfer;
use App\Telegram\Handlers\Commands;
use App\Telegram\Handlers\Help;
use App\Telegram\Middleware\ForDevelopers;
use App\Telegram\Middleware\ForLeaders;
use App\Telegram\Middleware\ForPrivate;
use App\Telegram\Middleware\MaintenanceMode;
use App\Telegram\Middleware\Registered;
use App\Telegram\Middleware\RetrieveAccount;
use SergiX44\Nutgram\Nutgram;

$bot->middleware(MaintenanceMode::class);
$bot->middleware(RetrieveAccount::class);

$bot->onCommand('start{param}', Help::class);
$bot->onInlineQuery([Timetable::class, 'query']);

$bot->group(function (Nutgram $bot) {

    $bot->onCommand('timetable', Timetable::class);
    $bot->onCommand('group', Group::class);
    $bot->onCommand('transfer', Transfer::class)->middleware(ForLeaders::class);
    $bot->onCommand('commands', Commands::class)->middleware(ForDevelopers::class);

})->middleware(ForPrivate::class)->middleware(Registered::class);
