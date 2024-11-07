<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $primaryKey = 'certificate_ID';
    protected $fillable = ['professional_ID', 'certificate_name', 'certificate_date', 'certificate_image'];

    public function professional()
    {
        return $this->belongsTo(Professional::class, 'professional_ID');
    }
}
