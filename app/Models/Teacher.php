<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property string $name
 * @method static self create(array $attributes)
 */
class Teacher extends \Illuminate\Foundation\Auth\User
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
    ];

    public int $migrationOrder = 1;
    public function migration(Blueprint $table): void
    {
        $table->id();
        $table->timestamps();
        $table->string('name')->unique();
    }

    public function pairs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Pair::class);
    }
}
