<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    use HasFactory;

    protected $primaryKey = 'refund_ID';
    protected $fillable = ['payment_ID', 'paymentdate', 'refunddate', 'status'];

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_ID');
    }
}
