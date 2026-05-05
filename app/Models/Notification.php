<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'firm_id', 'user_id', 'notifiable_id', 'notifiable_type',
        'type', 'title', 'message', 'is_read', 'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function firm()
    {
        return $this->belongsTo(Firm::class);
    }

    public function notifiable()
    {
        return $this->morphTo();
    }
}
