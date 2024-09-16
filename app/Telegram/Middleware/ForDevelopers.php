<?php

namespace App\Telegram\Middleware;

use SergiX44\Nutgram\Nutgram;

class ForDevelopers
{
    public function __invoke(Nutgram $bot, $next): void
    {
        if (in_array($bot->chatId(), explode(',', env('TELEGRAM_DEVELOPERS'))))
            $next($bot);
    }
}
