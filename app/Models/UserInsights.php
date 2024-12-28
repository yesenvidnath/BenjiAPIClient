<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInsights extends Model
{
    use HasFactory;

    protected $table = 'user_insights';

    protected $fillable = [
        'user_ID',
        'forecasting_message',
        'insights',
        'saving_percentage',
        'spending_percentage',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_ID', 'user_ID');
    }
}
