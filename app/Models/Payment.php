<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $primaryKey = 'payment_ID';
    protected $fillable = ['datetime', 'amount', 'user_ID', 'meeting_ID'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_ID');
    }

    public function meeting()
    {
        return $this->belongsTo(Meeting::class, 'meeting_ID');
    }

    public function refund()
    {
        return $this->hasOne(Refund::class, 'payment_ID');
    }
}
