<?php

namespace App\Telegram\Commands;

use App\Services\TimetableService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Handlers\Type\Command;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Inline\InlineQueryResultArticle;
use SergiX44\Nutgram\Telegram\Types\Inline\InlineQueryResultsButton;
use SergiX44\Nutgram\Telegram\Types\Input\InputTextMessageContent;

class Timetable extends Command
{
    protected string $command = 'timetable';

    protected ?string $description = 'Узнать нормальное расписание';

    public function handle(Nutgram $bot): void
    {
        \App\Telegram\Conversations\Timetable::begin($bot);
    }

    public function query(Nutgram $bot): void
    {
        try {

            $d = Carbon::parseFromLocale($bot->inlineQuery()->query ?? 'today', 'ru');
            if ($d < Carbon::parse('02.09.2024') || $d > Carbon::parse('01.02.2025'))
                return;

        } catch (\Throwable $e) {

            return;

        }

        $group = Auth::user()?->group;
        $timetableService = App::make(TimetableService::class);

        $bot->asResponse()->answerInlineQuery(
            results: $group ? [InlineQueryResultArticle::make(
                id: "timetable:{$d->format('d.m.Y')}",
                title: "Расписание",
                input_message_content: InputTextMessageContent::make(
                    message_text: $timetableService->getTimetable($group, $d)->text,
                    parse_mode: ParseMode::HTML,
                    disable_web_page_preview: true,
                ),
                description: $d->translatedFormat('l, d.m.Y'),
            )] : [],
            cache_time: 0,
            is_personal: true,
            button: !$group ? InlineQueryResultsButton::make('Указать группу', start_parameter: 'group') : null,
        );
    }
}
