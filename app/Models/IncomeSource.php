<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class IncomeSource extends Model
{
    use HasFactory;

    protected $primaryKey = 'income_source_ID';
    protected $fillable = ['user_ID', 'source_name', 'amount', 'frequency', 'description'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_ID');
    }
}
