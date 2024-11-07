<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'user_ID';

    protected $fillable = [
        'type',
        'DOB',
        'phone_number',
        'email',
        'password',
        'profile_image',
        'bank_choice'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'DOB' => 'date',
    ];

    public function professional()
    {
        return $this->hasOne(Professional::class, 'user_ID');
    }

    public function customer()
    {
        return $this->hasOne(Customer::class, 'user_ID');
    }

    public function admin()
    {
        return $this->hasOne(Admin::class, 'user_ID');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'user_ID');
    }

    public function incomeSources()
    {
        return $this->hasMany(IncomeSource::class, 'user_ID');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_ID');
    }
}
