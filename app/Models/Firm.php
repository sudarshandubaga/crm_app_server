<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Firm extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'logo', 'category', 'expire_at', 'settings', 'timezone', 'plan_id', 'has_used_first_time_offer',
    ];

    protected $casts = [
        'settings'  => 'array',
        'expire_at' => 'datetime', // Use datetime (not date) so API returns full ISO 8601 string
        'has_used_first_time_offer' => 'boolean',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function pipelines()
    {
        return $this->hasMany(Pipeline::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function customFieldCategories()
    {
        return $this->hasMany(CustomFieldCategory::class);
    }
}
