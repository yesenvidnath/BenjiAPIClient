<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YearlyChartData extends Model
{
    use HasFactory;
    protected $table = 'yearly_chart_data';

    protected $fillable = ['user_ID', 'month_name', 'expense'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_ID', 'user_ID');
    }
}
