<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'firm_id',
        'plan_id',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
        'amount',
        'status',
    ];

    public function firm()
    {
        return $this->belongsTo(Firm::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
