<?php

namespace App\Policies;

use App\Models\Day;
use App\Models\Chat;

class DayPolicy
{
    public function update(Chat $chat, Day $day): bool
    {
        return $day->group->is($chat->leadership) || in_array($chat->chat_id, explode(',', config('nutgram.developers')));
    }
}
