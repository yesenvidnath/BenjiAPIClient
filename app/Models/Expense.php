<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $primaryKey = 'expenses_ID';
    protected $fillable = ['user_ID', 'expenseslist_ID', 'comment'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_ID');
    }

    public function expensesList()
    {
        return $this->belongsTo(ExpensesList::class, 'expenseslist_ID');
    }
}
