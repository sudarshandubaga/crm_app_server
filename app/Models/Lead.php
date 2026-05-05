<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'firm_id', 'assigned_to', 'contact_id',
        'pipeline_id', 'stage_id', 'source',
    ];

    public function firm()
    {
        return $this->belongsTo(Firm::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function pipeline()
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function stage()
    {
        return $this->belongsTo(Stage::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function customFieldValues()
    {
        return $this->morphMany(CustomFieldValue::class, 'model');
    }

    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }
}
