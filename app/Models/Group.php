<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property string $number
 * @property Chat $leader
 * @property Collection $chats
 * @property Collection $days
 */
class Group extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'number',
        'chat_id',
    ];

    public int $migrationOrder = 1;
    public function migration(Blueprint $table): void
    {
        $table->id();
        $table->timestamps();
        $table->foreignIdFor(Chat::class, 'leader_id')->nullable();
        $table->string('number')->unique();
    }

    public function leader(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    public function chats(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Chat::class);
    }

    public function days(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Day::class);
    }
}
