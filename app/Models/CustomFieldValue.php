<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomFieldValue extends Model
{
    protected $fillable = [
        'custom_field_id', 'model_id', 'model_type', 'value',
    ];

    public function customField()
    {
        return $this->belongsTo(CustomField::class);
    }

    public function model()
    {
        return $this->morphTo();
    }
}
