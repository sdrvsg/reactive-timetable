<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;

/**
 * @property int $id
 * @property Carbon $date
 * @property string $comment
 *
 * @property string $text
 * @property int $week
 * @property bool $is_odd
 *
 * @property Group $group
 * @property Collection $pairs
 * @property Collection $teachers
 * @property Collection $maggots
 * @method static self find(int $id)
 */
class Day extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'date',
        'comment',
        'group_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
        ];
    }

    public int $migrationOrder = 2;
    public function migration(Blueprint $table): void
    {
        $table->id();
        $table->timestamps();
        $table->foreignIdFor(Group::class)->constrained()->cascadeOnDelete();
        $table->date('date');
        $table->text('comment')->nullable();
    }

    public function group(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function pairs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Pair::class)->orderBy('number');
    }

    public function maggots(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Maggot::class);
    }

    public function text(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                $date = mb_ucfirst($this->date->translatedFormat('l, d.m.Y'));
                $leader = $this->group->leader->chat_id;
                $leader_name = $this->group->leader->username ?? __('timetable.day.leader');

                $c = $this->comment ? __('timetable.day.comment', [
                    'comment' => $this->comment,
                    'leader' => $leader,
                    'name' => $leader_name
                ]) : '';

                $pairs = [];
                foreach ($this->pairs->groupBy('number') as $number => $pair) {
                    $pairs[] = __('timetable.day.pair', [
                        'time' => $this->getTimeString($number),
                        'pairs' => $pair->map(fn (Pair $pair) => $pair->text)->implode("\n\n"),
                    ]);
                }

                $maggots = $this->maggots->groupBy('maggotable')->sortBy(fn (Collection $m) => count($m), descending: true)->take(3);
                $m = [];
                $place = 0;
                Log::info($maggots);

                foreach ($maggots as $values)
                    $m[] = __('timetable.day.maggot', [
                        'place' => match (++$place) {1 => '🥇', 2 => '🥈', 3 => '🥉'},
                        'maggot' => $values->first()->maggotable->identifier ?? $values->first()->maggotable->name,
                    ]);

                return __('timetable.day.text', [
                    'date' => $date,
                    'week' => $this->week,
                    'odd_even' => $this->is_odd ? __('timetable.day.odd') : __('timetable.day.even'),
                    'group' => $this->group->number,
                    'pairs' => implode("\n", $pairs) ?: __('timetable.day.weekend'),
                    'comment' => $c,
                    'maggots' => $place ? __('timetable.day.maggots', [
                        'maggots' => implode("\n", $m),
                    ]) : '',
                ]);
            }
        );
    }

    public function week(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                return floor($this->date->diffInWeeks(\Illuminate\Support\Carbon::parse(config('app.start_date')), absolute: true) + 1);
            }
        );
    }

    public function isOdd(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                return boolval($this->week % 2);
            }
        );
    }

    protected function getTimeString(int $number): string
    {
        return match ($number) {
            1 => __('timetable.pair.time', ['icon' => '😵‍💫', 'number' => $number, 'time' => '8:00 — 9:35']),
            2 => __('timetable.pair.time', ['icon' => '😵‍💫', 'number' => $number, 'time' => '9:45 — 11:20']),
            3 => __('timetable.pair.time', ['icon' => '🙃', 'number' => $number, 'time' => '11:30 — 13:05']),
            4 => __('timetable.pair.time', ['icon' => '🙃', 'number' => $number, 'time' => '13:30 — 15:05']),
            5 => __('timetable.pair.time', ['icon' => '😞', 'number' => $number, 'time' => '15:15 — 16:50']),
            6 => __('timetable.pair.time', ['icon' => '😞', 'number' => $number, 'time' => '17:00 — 18:35']),
            7 => __('timetable.pair.time', ['icon' => '🤩', 'number' => $number, 'time' => '18:45 — 20:15']),
            8 => __('timetable.pair.time', ['icon' => '🤩', 'number' => $number, 'time' => '20:25 — 21:55']),
        };
    }
}
