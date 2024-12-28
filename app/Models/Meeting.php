<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    use HasFactory;

    protected $primaryKey = 'meeting_ID';
    protected $fillable = ['time_date', 'user_ID_customer', 'user_ID_professional', 'meet_url', 'status'];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'user_ID_customer');
    }

    public function professional()
    {
        return $this->belongsTo(Professional::class, 'user_ID_professional');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'meeting_ID');
    }
}
