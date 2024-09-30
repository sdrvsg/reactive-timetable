<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property Day $day
 * @property Chat $chat
 * @property Chat|Teacher $maggotable
 */
class Maggot extends \Illuminate\Foundation\Auth\User
{
    use HasFactory, Notifiable;

    public int $migrationOrder = 3;
    public function migration(Blueprint $table): void
    {
        $table->id();
        $table->timestamps();

        $table->foreignIdFor(Day::class)->constrained()->cascadeOnDelete();
        $table->foreignIdFor(Chat::class)->constrained()->cascadeOnDelete();
        $table->morphs('maggotable');
    }

    public function day(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Day::class);
    }

    public function chat(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    public function maggotable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }
}
