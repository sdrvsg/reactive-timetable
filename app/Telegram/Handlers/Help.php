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

        $bot->asResponse()->sendImagedMessage(__('handlers.help', [
            'version' => config('app.version'),
        ]));
    }
}
