<?php

namespace App\Telegram\Handlers;

use App\Models\Chat;
use App\Models\Pair;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Inline\InlineQueryResultArticle;
use SergiX44\Nutgram\Telegram\Types\Inline\InlineQueryResultsButton;
use SergiX44\Nutgram\Telegram\Types\Input\InputTextMessageContent;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class Maggot
{
    public function __invoke(Nutgram $bot, ?string $query = null): void
    {
        $group = Auth::user()?->group;
        $query = trim($query ?? '');

        $teachers = $group?->days()
            ->whereDate('date', now())
            ->first()
            ->pairs
            ->map(fn (Pair $pair) => $pair->teacher)
            ->where(fn (Teacher $teacher) => !$query || str_contains(mb_strtolower($teacher->name), mb_strtolower($query)))
            ->unique()
            ->map(fn (Teacher $teacher) => InlineQueryResultArticle::make(
                id: "teacher:$teacher->id",
                title: $teacher->name,
                input_message_content: InputTextMessageContent::make(
                    message_text: __('maggot.voted', ['candidate' => $teacher->name]),
                    parse_mode: ParseMode::HTML,
                    disable_web_page_preview: true,
                ),
                reply_markup: InlineKeyboardMarkup::make()->addRow(
                    InlineKeyboardButton::make('Голосовать', switch_inline_query_current_chat: "p $teacher->name"),
                ),
                description: __('maggot.candidate'),
            ))
            ->values()
            ->all() ?: [];

        $groupmates = [];
        if ($query)
            $groupmates = $group?->chats()
                ->where(function (Builder $builder) use ($query) {
                    $builder->where('username', 'like', "%$query%");
                    $builder->orWhere('name', 'like', "%$query%");
                    $builder->orWhere('chat_id', $query);
                })
                ->get()
                ->map(fn (Chat $chat) => InlineQueryResultArticle::make(
                    id: "chat:$chat->id",
                    title: $chat->identifier,
                    input_message_content: InputTextMessageContent::make(
                        message_text: __('maggot.voted', ['candidate' => $chat->identifier]),
                        parse_mode: ParseMode::HTML,
                        disable_web_page_preview: true,
                    ),
                    reply_markup: InlineKeyboardMarkup::make()->addRow(
                        InlineKeyboardButton::make('Голосовать', switch_inline_query_current_chat: "p $chat->identifier"),
                    ),
                    description: __('maggot.candidate'),
                ))
                ->values()
                ->all() ?: [];

        $bot->asResponse()->answerInlineQuery(
            results: $teachers + $groupmates,
            cache_time: 0,
            is_personal: true,
            button: !$group ? InlineQueryResultsButton::make(__('timetable.inline.button'), start_parameter: 'group') : null,
        );
    }

    public function vote(Nutgram $bot): void
    {
        [$type, $id] = explode(':', $bot->chosenInlineResult()->result_id);
        $type = match ($type) {
            'teacher' => Teacher::class,
            default => Chat::class,
        };

        $maggotable = $type::find(intval($id));
        $user = Auth::user();

        if (!$user || !$maggotable)
            return;

        $day = $user->group->days()->whereDate('date', now())->first();
        $day->maggots()->where('chat_id', $user->id)->delete();

        $maggot = new \App\Models\Maggot;
        $maggot->day()->associate($day);
        $maggot->chat()->associate($user);
        $maggot->maggotable()->associate($maggotable);
        $maggot->save();
    }
}
