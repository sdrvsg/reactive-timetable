<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Nutgram;

class TelegramController extends Controller
{
    public function __invoke(Nutgram $bot): void
    {
        try {

            $bot->run();

        } catch (\Throwable $e) {

            Log::channel('telegram')->error("{$e->getMessage()}\n{$e->getFile()}\n{$e->getLine()}");

        }
    }
}
