<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'firm_id', 'user_id', 'subject_id', 'subject_type',
        'action', 'properties',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function firm()
    {
        return $this->belongsTo(Firm::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->morphTo();
    }
}
