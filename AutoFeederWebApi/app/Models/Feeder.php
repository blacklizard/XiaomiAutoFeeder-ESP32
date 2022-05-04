<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Feeder extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'feeders';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'ip_address',
        'mac_address',
        'schedule_synced'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'schedule_synced' => 'boolean',
        'drier_replaced_at' => 'datetime'
    ];

    /**
     * Announcement for this feeder
     *
     * @return HasMany
     */
    public function announcements(): HasMany
    {
        return $this->hasMany(FeederAnnounce::class);
    }

    /**
     * Schedules for this feeder
     *
     * @return HasMany
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(FeederSchedule::class);
    }

    /**
     * The group that belong to the feeder.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
