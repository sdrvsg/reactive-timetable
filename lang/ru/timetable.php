<?php

return [
    'day' => [
        'text' => "<b>:date</b> для группы <b>:group</b>\n\n:pairs:comment",
        'weekend' => "<b>🎉🎉🎉 Пар нет</b>",
        'comment' => "\n\n<blockquote>:comment\n— <a href='tg://user?id=:leader'>:name</a></blockquote>",
        'leader' => "Староста",
    ],
    'pair' => [
        'text' => "<b>:time</b>\n:icon <b>:name</b>\n:teacher\n<i>:place</i>:groups",
        'groups' => "\n<i>:groups</i>",
        'time' => ":icon :number пара (:time)",
        'blanks' => [
            'name' => "Предмет не указан",
            'place' => "Место проведения не указано",
            'teacher' => "Преподаватель не указан",
        ],
    ],
    'inline' => [
        'title' => "Расписание",
        'button' => "Указать группу",
    ],
];
