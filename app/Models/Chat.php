<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Notifications\Notifiable;

/**
 * @property string $chat_id
 * @property string $username
 * @property string $identifier
 * @property boolean $is_blocked
 * @property Group $group
 * @property Group $leadership
 */
class Chat extends \Illuminate\Foundation\Auth\User
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'chat_id',
        'username',
        'group_id',
    ];

    protected function casts(): array
    {
        return [
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
        $table->boolean('is_blocked')->default(false);
    }

    public function group(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function leadership(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Group::class, 'leader_id');
    }

    public function identifier(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $this->username ?? $this->chat_id
        );
    }
}
