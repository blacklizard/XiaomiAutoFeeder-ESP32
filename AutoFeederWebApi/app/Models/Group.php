<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'groups';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Feeders for this group
     *
     * @return HasMany
     */
    public function feeders(): HasMany
    {
        return $this->hasMany(Feeder::class);
    }

    /**
     * Schedules for this group
     *
     * @return HasMany
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(GroupSchedule::class);
    }
}
