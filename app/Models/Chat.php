<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property string $chat_id
 * @property string $username
 * @property string $name
 * @property string $identifier
 * @property boolean $is_blocked
 * @property Carbon $was_online_at
 * @property Group $group
 * @property Group $leadership
 */
class Chat extends \Illuminate\Foundation\Auth\User
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'chat_id',
        'username',
        'name',
        'group_id',
        'was_online_at',
    ];

    protected function casts(): array
    {
        return [
            'was_online_at' => 'datetime',
            'is_blocked' => 'boolean',
        ];
    }

    public int $migrationOrder = 2;
    public function migration(Blueprint $table): void
    {
        $table->id();
        $table->timestamps();
        $table->foreignIdFor(Group::class)->constrained()->cascadeOnDelete();

        $table->string('chat_id')->unique();
        $table->string('username')->nullable();
        $table->string('name')->nullable();
        $table->boolean('is_blocked')->default(false);
        $table->timestamp('was_online_at')->nullable();
    }

    public function group(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function leadership(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Group::class, 'leader_id');
    }

    public function maggots(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Maggot::class, 'maggotable');
    }

    public function identifier(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $this->name ?? $this->username ?? $this->chat_id
        );
    }
}
