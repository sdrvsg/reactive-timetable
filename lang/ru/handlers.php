<?php

return [
    'help' => "
<b>👋 Этот бот поможет следить за расписанием, вносить в него изменения и уведомлять всех одногруппников об этом!</b>

<b>В <i>Приватном</i> режиме доступны:</b>

/start — Информация (вы тут)
/timetable — Расписание
/group — Сменить группу
/transfer — Передать старосту

<b>Для групп только <i>Инлайн</i> режим:</b>

<code>@ssauReactiveBot {сегодня, завтра, 11 сен, 15.12.2024...}</code>
<i>Скинуть расписание указанной даты в чат</i>

<code>@ssauReactiveBot p {Имя препода или одногруппника}</code>
<i>Проголосовать за пидораса текущего дня</i>

<blockquote>Бот находится в состоянии beta-теста, просьба баги и предложения кидать на <a href='https://github.com/sdrvsg/reactive-timetable/issues'>GitHub</a>

Версия: :version</blockquote>
",
    'buttons' => [
        'today' => "Сегодня",
        'post' => "Пост",
        'refresh' => "🔄",
        'edit' => "Пары",
        'deadlines' => "Дедлайн",
        'send' => "Отправить",
        'back' => "Назад",
        'type' => "Вид занятия",
        'name' => "Название",
        'teacher' => "Преподаватель",
        'groups' => "Группы",
        'place' => "Место проведения",
        'number' => "Время",
        'delete' => "Удалить",
        'add' => "Добавить",
        'yes' => "Да",
        'subject' => "Предмет",
        'description' => "Описание",
    ],
    'register' => [
        'start' => "<b>👋 Привет!</b>\n\nЧтобы начать пользоваться ботом, введи номер своей группы ниже:\n\n<b>Формат:</b> <i>xxxx-xxxxxxD</i>",
        'error' => "<b>⛔️ Неверный формат!</b>\nНужно: <i>xxxx-xxxxxxD</i>",
        'back' => "Вернуться",
        'success' => "<b>Отлично!</b>\n\nТеперь можешь ввести /timetable для получения расписания",
    ],
    'group' => [
        'start' => "<b>⏭ Чтобы сменить группу, введи ее ниже</b>\n\n<b>Формат:</b> <i>xxxx-xxxxxxD</i>",
        'error' => "<b>⛔️ Неверный формат!</b>\nНужно: <i>xxxx-xxxxxxD</i>",
        'gay' => "<b>⛔️ Да ты пидор</b>",
        'success' => "<b>Отлично!</b>\n\nГруппа <b>:new</b> покупает вас у группы <b>:old</b> за <i>$:amount</i>",
    ],
    'transfer' => [
        'start' => "<b>👋 Введите ID вашего одногруппника, чтобы передать ему права старосты (юзернейм копируется при нажатии)</b>\n\n:chats",
        'chat' => "<code>:name</code>: <a href='tg://user?id=:id'>Профиль</a>",
        'error' => "<b>⛔️ Что-то не то ввели</b>",
        'success' => "<b>Отлично!</b>\n\nВы больше не староста 🎉🎉🎉",
    ],
    'timetable' => [
        'edited' => "ред.",
        'comment' => ":timetable\n\n👉 Все одногруппники получат уведомления\nМожно отправить необязательный комментарий",
    ],
    'day' => [
        'start' => ":timetable\n\n👉 Какую пару нужно изменить?",
        'deadlines' => ":timetable\n\n👉 Какой дедлайн поменять?",
        'deadline' => ":timetable\n\n👉 Введите название предмета",
    ],
    'pair' => [
        'start' => ":timetable\n\n👉 Что поменять?",
        'number' => ":timetable\n\n👉 На какое время перенести пару?",
        'type' => ":timetable\n\n👉 Укажи вид занятия",
        'name' => ":timetable\n\n👉 Введи название пары\nТекущее название: <code>:value</code>",
        'teacher' => ":timetable\n\n👉 Введи преподавателя\nСейчас: <code>:value</code>",
        'place' => ":timetable\n\n👉 Введи место проведения\nСейчас: <code>:value</code>",
        'groups' => ":timetable\n\n👉 Введи группы / подгруппы\nСейчас: <code>:value</code>",
        'delete' => ":timetable\n\n👉 Точно отменить пару?",
    ],
    'deadline' => [
        'start' => ":timetable\n\n👉 Что поменять?",
        'subject' => ":timetable\n\n👉 Введите название предмета\nТекущее название: <code>:value</code>",
        'description' => ":timetable\n\n👉 Укажите описание\nТекущее описание:\n\n:value",
        'delete' => ":timetable\n\n👉 Точно отменить дедлайн?",
    ],
];
