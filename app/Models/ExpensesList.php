<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpensesList extends Model
{
    use HasFactory;

    protected $primaryKey = 'expenseslist_ID';
    protected $fillable = ['reason_ID'];

    public function reason()
    {
        return $this->belongsTo(Reason::class, 'reason_ID');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'expenseslist_ID');
    }
}
