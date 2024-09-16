<?php

namespace App\Models;

use App\Enums\PairType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Notifications\Notifiable;

/**
 * @property integer $id
 * @property string $text
 * @property string $icon
 * @property string $time_string
 * @property integer $number
 * @property boolean $is_present
 * @property ?PairType $type
 * @property string $name
 * @property string $place
 * @property string $teacher
 * @property string $groups
 * @property Day $day
 * @method static self find(int $id)
 */
class Pair extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'number',
        'is_present',
        'type',
        'name',
        'place',
        'teacher',
        'groups',
        'day_id',
    ];

    protected function casts(): array
    {
        return [
            'is_present' => 'boolean',
            'type' => PairType::class,
        ];
    }

    public int $migrationOrder = 3;
    public function migration(Blueprint $table): void
    {
        $table->id();
        $table->timestamps();
        $table->foreignIdFor(Day::class)->constrained()->cascadeOnDelete();

        $table->integer('number');
        $table->boolean('is_present')->default(false);
        $table->string('type')->nullable();
        $table->string('name')->nullable();
        $table->string('place')->nullable();
        $table->string('teacher')->nullable();
        $table->string('groups')->nullable();
    }

    public function day(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Day::class);
    }

    public function text(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                $n = $this->name ?? 'Предмет не указан';
                $t = $this->teacher ?? 'Преподаватель не указан';
                $p = $this->place ?? 'Место проведения не указано';
                $g = $this->groups ? "\n<i>$this->groups</i>" : '';
                return "<b>$this->time_string</b>\n$this->icon <b>$n</b>\n$t\n<i>$p</i>$g";
            }
        );
    }

    public function timeString(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                return match ($this->number) {
                    1 => "😵‍💫 $this->number пара (8:00 — 9:35)",
                    2 => "😵‍💫 $this->number пара (9:45 — 11:20)",
                    3 => "🙃 $this->number пара (11:30 — 13:05)",
                    4 => "🙃 $this->number пара (13:30 — 15:05)",
                    5 => "😞 $this->number пара (15:15 — 16:50)",
                    6 => "😞 $this->number пара (17:00 — 18:35)",
                    7 => "🤩 $this->number пара (18:45 — 20:15)",
                    8 => "🤩 $this->number пара (20:25 — 21:55)",
                };
            }
        );
    }

    public function icon(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                return match ($this->type) {
                    PairType::LECTURE => '🎉',
                    PairType::LAB => '🕹',
                    PairType::PRACTICE => '💩',
                    PairType::OTHER => '🔫',
                    default => '❓',
                };
            }
        );
    }
}
