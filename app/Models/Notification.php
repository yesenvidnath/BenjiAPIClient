<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $primaryKey = 'notification_ID';
    protected $fillable = ['user_ID', 'type', 'message', 'is_read'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_ID');
    }
}
