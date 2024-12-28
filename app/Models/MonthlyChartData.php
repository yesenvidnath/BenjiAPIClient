<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyChartData extends Model
{
    use HasFactory;
    protected $table = 'monthly_chart_data';

    protected $fillable = ['user_ID', 'week_name', 'expense'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_ID', 'user_ID');
    }
}
