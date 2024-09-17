<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property Carbon $date
 * @property string $text
 * @property string $comment
 * @property Group $group
 * @property Collection $pairs
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

                $pairs = $this->pairs()
                    ->where('is_present', true)
                    ->get()
                    ->map(fn (Pair $pair) => $pair->text)
                    ->implode("\n\n") ?: __('timetable.day.weekend');

                return __('timetable.day.text', [
                    'date' => $date,
                    'group' => $this->group->number,
                    'pairs' => $pairs,
                    'comment' => $c,
                ]);
            }
        );
    }
}
