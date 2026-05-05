<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stage extends Model
{
    protected $fillable = ['name', 'pipeline_id', 'color'];

    public function pipeline()
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }
}
