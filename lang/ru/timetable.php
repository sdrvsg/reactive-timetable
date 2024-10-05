<?php

return [
    'day' => [
        'text' => "<b>:date</b> для группы <b>:group</b>\n<i>Неделя: :week, :odd_even</i>\n\n:pairs\n\n<b>Дедлайны:</b>\n\n:deadlines:comment:maggots",
        'odd' => 'нечетная',
        'even' => 'четная',
        'pair' => "<blockquote><b>:time</b>\n\n:pairs</blockquote>",
        'weekend' => "<b>🎉🎉🎉 Пар нет</b>",
        'procrastination' => "<b>🎉🎉🎉 Дедлайнов нет</b>",
        'comment' => "\n\n<blockquote>:comment\n— <a href='tg://user?id=:leader'>:name</a></blockquote>",
        'leader' => "Староста",
        'maggots' => "\n\n<b>🏆 Пидорасы дня:</b>\n:maggots",
        'maggot' => "<i>:place :maggot</i>",
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
    'deadline' => [
        'text' => "<blockquote expandable><b>:subject</b>\n:description</blockquote>",
    ],
    'inline' => [
        'title' => "Расписание",
        'button' => "Указать группу",
    ],
];
