<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'firm_id', 'is_pinned', 'first_name', 'middle_name', 'last_name',
        'email', 'mobile', 'gender', 'dob', 'phone',
        'address', 'city', 'state', 'zip', 'country', 'notes',
    ];

    protected $casts = [
        'dob' => 'date',
        'is_pinned' => 'boolean',
    ];

    public function firm()
    {
        return $this->belongsTo(Firm::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function customFieldValues()
    {
        return $this->morphMany(CustomFieldValue::class, 'model');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }
}
