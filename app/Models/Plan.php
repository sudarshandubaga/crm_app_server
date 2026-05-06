<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'amount',
        'reg_amount',
        'duration_months',
        'description',
        'is_first_time_only',
        'is_active',
    ];
}
