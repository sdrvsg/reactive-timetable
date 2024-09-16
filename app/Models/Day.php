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
                $leader_name = $this->group->leader->username ?? 'Староста';
                $c = $this->comment ? "\n\n<blockquote>$this->comment\n— <a href='tg://user?id=$leader'>$leader_name</a></blockquote>" : '';

                $pairs = $this->pairs()
                    ->where('is_present', true)
                    ->get()
                    ->map(fn (Pair $pair) => $pair->text)
                    ->implode("\n\n") ?: '<b>🎉🎉🎉 Пар нет</b>';

                return "<b>$date</b> для группы <b>{$this->group->number}</b>\n\n$pairs$c";
            }
        );
    }
}
