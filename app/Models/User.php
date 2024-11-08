<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'user_ID';
    protected $keyType = 'int';

    protected $fillable = [
        'type', 'DOB', 'phone_number', 'email', 'password', 'profile_image', 'bank_choice',
    ];

    protected $hidden = [
        'password', 'remember_token',
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

    /**
     * Override the save method to add debugging in case of issues.
     */
    public function save(array $options = [])
    {
        Log::info('Saving user:', ['id' => $this->id, 'email' => $this->email]);
        parent::save($options);
    }

    /**
     * Generate a new API token for the user.
     */
    public function generateApiToken()
    {
        return $this->createToken('api_token')->plainTextToken;
    }
}
