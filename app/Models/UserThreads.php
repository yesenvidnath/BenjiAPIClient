<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserThreads extends Model
{
    use HasFactory;

    protected $table = 'user_threads';

    protected $fillable = ['user_ID', 'thread_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_ID', 'user_ID');
    }
}
