<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $primaryKey = 'category_ID';
    protected $fillable = ['category'];

    public function reasons()
    {
        return $this->hasMany(Reason::class, 'category_ID');
    }
}
