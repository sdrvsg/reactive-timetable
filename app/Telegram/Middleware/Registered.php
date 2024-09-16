<?php

namespace App\Telegram\Middleware;

use App\Telegram\Conversations\Register;
use Illuminate\Support\Facades\Auth;
use SergiX44\Nutgram\Nutgram;

class Registered
{
    public function __invoke(Nutgram $bot, $next): void
    {
        if (!Auth::check()) {

            Register::begin($bot);
            return;

        }

        $next($bot);
    }
}
