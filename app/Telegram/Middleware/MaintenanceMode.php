<?php

namespace App\Telegram\Middleware;

use SergiX44\Nutgram\Nutgram;

class MaintenanceMode
{
    public function __invoke(Nutgram $bot, $next): void
    {
        if (!config('app.closed') || in_array($bot->userId(), explode(',', config('nutgram.developers'))))
            $next($bot);
        else $bot->sendImagedMessage('<b>Ведутся технические работы</b>');
    }
}
