<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomFieldCategory extends Model
{
    protected $fillable = ['name', 'firm_id', 'for'];

    public function firm()
    {
        return $this->belongsTo(Firm::class);
    }

    public function customFields()
    {
        return $this->hasMany(CustomField::class);
    }
}
