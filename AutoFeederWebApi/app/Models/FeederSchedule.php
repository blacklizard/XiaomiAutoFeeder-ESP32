<?php

namespace App\Models;

use App\Concerns\ScheduleConcerns;
use App\Enums\AnnounceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeederSchedule extends Model
{
    use HasFactory;
    use ScheduleConcerns;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'feeder_schedules';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'unit',
        'time',
        'enable',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'enable' => 'boolean',
    ];
}
