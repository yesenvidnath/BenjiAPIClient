<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $primaryKey = 'user_ID';
    protected $fillable = ['status'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_ID');
    }

    public function meetings()
    {
        return $this->hasMany(Meeting::class, 'user_ID_customer');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'user_ID');
    }
}
