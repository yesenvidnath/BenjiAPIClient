<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Professional extends Model
{
    use HasFactory;

    protected $primaryKey = 'user_ID';
    protected $fillable = ['certificate_ID', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_ID');
    }

    public function meetings()
    {
        return $this->hasMany(Meeting::class, 'user_ID_professional');
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class, 'professional_ID');
    }
}
