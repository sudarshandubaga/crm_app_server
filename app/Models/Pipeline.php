<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pipeline extends Model
{
    protected $fillable = ['name', 'firm_id'];

    public function firm()
    {
        return $this->belongsTo(Firm::class);
    }

    public function stages()
    {
        return $this->hasMany(Stage::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }
}
