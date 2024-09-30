<?php

return [
    'day' => [
        'text' => "<b>:date</b> для группы <b>:group</b>\n\n:pairs:comment",
        'pair' => "<blockquote><b>:time</b>\n\n:pairs</blockquote>",
        'weekend' => "<b>🎉🎉🎉 Пар нет</b>",
        'comment' => "\n\n<blockquote>:comment\n— <a href='tg://user?id=:leader'>:name</a></blockquote>",
        'leader' => "Староста",
    ],
    'pair' => [
        'text' => ":icon <b>:name</b> (<i>:place</i>)\n:teacher:groups",
        'groups' => " <i>(:groups)</i>",
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
