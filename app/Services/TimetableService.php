<?php

namespace App\Services;

use App\Models\Day;
use App\Models\Group;
use App\Models\Pair;
use App\Models\Teacher;
use Carbon\Carbon;
use Carbon\WeekDay;
use Illuminate\Support\Facades\Process;

class TimetableService
{
    public function getTimetable(Group $group, Carbon $date, bool $refresh = false): Day
    {
        $day = $group->days()->whereDate('date', $date)->first();
        $day = $day ?? Day::query()->create(['date' => $date, 'group_id' => $group->id]);

        if ($refresh || !$day->pairs()->exists()) {

            $day->pairs()->delete();
            if ($refresh) {

                $day->comment = null;
                $day->save();

            }

            if ($date->weekday() !== WeekDay::Sunday->value) {

                $result = Process::path(base_path('scripts'))->run(". .venv/bin/activate; python timetable.py $group->number {$date->format('d.m.Y')}");
                $pairs = json_decode($result->output(), true);

                foreach ($pairs['data'] as $pair) {

                    $teacher = Teacher::query()->firstOrCreate(['name' => $pair['teacher']]);
                    Pair::create($pair + [
                        'day_id' => $day->id,
                        'teacher_id' => $teacher->id,
                    ]);

                }

            }

        }

        return $day;
    }

    public function getWeekday(Carbon $date, int $weekday): Carbon
    {
        return $date
            ->copy()
            ->subDays($date->getDaysFromStartOfWeek(WeekDay::Monday))
            ->addDays($weekday)
            ->subDay();
    }

    public function getWeekdayName(Carbon $date, int $weekday): string
    {
        $d = $date
            ->copy()
            ->subDays($date->getDaysFromStartOfWeek(WeekDay::Monday))
            ->addDays($weekday)
            ->subDay();

        return match ($d->dayOfWeek) {
            WeekDay::Monday->value => $d->is($date) ? '• Пн' : 'Пн',
            WeekDay::Tuesday->value => $d->is($date) ? '• Вт' : 'Вт',
            WeekDay::Wednesday->value => $d->is($date) ? '• Ср' : 'Ср',
            WeekDay::Thursday->value => $d->is($date) ? '• Чт' : 'Чт',
            WeekDay::Friday->value => $d->is($date) ? '• Пт' : 'Пт',
            WeekDay::Saturday->value => $d->is($date) ? '• Сб' : 'Сб',
            default => '?',
        };
    }
}
