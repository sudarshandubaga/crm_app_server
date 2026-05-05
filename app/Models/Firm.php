<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Firm extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'logo', 'category', 'expire_at', 'settings', 'timezone',
    ];

    protected $casts = [
        'settings'  => 'array',
        'expire_at' => 'date',
    ];

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
