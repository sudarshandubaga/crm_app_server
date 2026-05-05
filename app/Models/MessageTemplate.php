<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    protected $fillable = [
        'firm_id', 'name', 'content',
    ];

    public function firm()
    {
        return $this->belongsTo(Firm::class);
    }
}
