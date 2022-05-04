<?php

namespace App\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Carbon;

trait ScheduleConcerns
{
    protected function time(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                return Carbon::createFromFormat('H:i:s', $value)->format('H:i');
            }
        );
    }
}
