<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Notifications\Notifiable;

/**
 * @property integer $id
 * @property string $text
 *
 * @property string $subject
 * @property ?string $description
 *
 * @property Day $day
 *
 * @method static self find(int $id)
 * @method static self create(array $attributes)
 */
class Deadline extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'subject',
        'description',
        'day_id',
    ];

    public int $migrationOrder = 3;
    public function migration(Blueprint $table): void
    {
        $table->id();
        $table->timestamps();
        $table->foreignIdFor(Day::class)->constrained()->cascadeOnDelete();

        $table->string('subject');
        $table->text('description')->nullable();
    }

    public function day(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Day::class);
    }

    public function text(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                return __('timetable.deadline.text', [
                    'subject' => $this->subject,
                    'description' => $this->description,
                ]);
            }
        );
    }
}
