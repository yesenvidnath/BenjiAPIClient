<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForecastData extends Model
{
    use HasFactory;

    protected $table = 'forecast_data';

    protected $fillable = [
        'user_ID',
        'monthly_expense',
        'total_expense',
        'total_income',
        'weekly_expense',
        'yearly_expense',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_ID', 'user_ID');
    }
}
