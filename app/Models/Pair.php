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
 *
 * @property string $text
 * @property string $icon
 *
 * @property integer $number
 * @property ?PairType $type
 * @property ?string $name
 * @property ?string $place
 * @property ?string $groups
 *
 * @property Day $day
 * @property Teacher $teacher
 *
 * @method static self find(int $id)
 * @method static self create(array $attributes)
 */
class Pair extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'number',
        'type',
        'name',
        'place',
        'groups',
        'day_id',
        'teacher_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => PairType::class,
        ];
    }

    public int $migrationOrder = 3;
    public function migration(Blueprint $table): void
    {
        $table->id();
        $table->timestamps();

        $table->foreignIdFor(Day::class)->constrained()->cascadeOnDelete();
        $table->foreignIdFor(Teacher::class)->nullable()->constrained()->nullOnDelete();

        $table->integer('number');
        $table->string('type')->nullable();
        $table->string('name')->nullable();
        $table->string('place')->nullable();
        $table->string('groups')->nullable();
    }

    public function day(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Day::class);
    }

    public function teacher(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function text(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                return __('timetable.pair.text', [
                    'icon' => $this->icon,
                    'name' => $this->name ?? __('timetable.pair.blanks.name'),
                    'teacher' => $this->teacher->name ?? __('timetable.pair.blanks.teacher'),
                    'place' => $this->place ?? __('timetable.pair.blanks.place'),
                    'groups' => $this->groups ? __('timetable.pair.groups', ['groups' => $this->groups]) : '',
                ]);
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
