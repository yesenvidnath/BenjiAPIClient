<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklyChartData extends Model
{
    use HasFactory;

    protected $table = 'weekly_chart_data';

    protected $fillable = ['user_ID', 'day_name', 'expense'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_ID', 'user_ID');
    }
}
