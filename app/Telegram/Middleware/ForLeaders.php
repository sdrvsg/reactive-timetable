<?php

namespace App\Telegram\Middleware;

use Illuminate\Support\Facades\Auth;
use SergiX44\Nutgram\Nutgram;

class ForLeaders
{
    public function __invoke(Nutgram $bot, $next): void
    {
        if (Auth::user()->leadership()->exists())
            $next($bot);
    }
}
