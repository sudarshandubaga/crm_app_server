<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'firm_id', 'user_id', 'event', 'description', 'ip', 'user_agent',
    ];

    public function firm()
    {
        return $this->belongsTo(Firm::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
