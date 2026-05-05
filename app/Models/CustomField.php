<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomField extends Model
{
    protected $fillable = [
        'custom_field_category_id', 'firm_id', 'label',
        'is_required', 'type', 'options', 'default_value', 'extra_attr',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(CustomFieldCategory::class, 'custom_field_category_id');
    }

    public function firm()
    {
        return $this->belongsTo(Firm::class);
    }

    public function values()
    {
        return $this->hasMany(CustomFieldValue::class);
    }
}
