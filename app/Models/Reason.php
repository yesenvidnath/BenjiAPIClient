<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reason extends Model
{
    use HasFactory;

    protected $primaryKey = 'reason_ID';
    protected $fillable = ['reason', 'category_ID'];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_ID');
    }

    public function expensesList()
    {
        return $this->hasMany(ExpensesList::class, 'reason_ID');
    }
}
