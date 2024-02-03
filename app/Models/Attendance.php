<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Attendance extends Model
{
    protected $fillable = ['check_in', 'check_out', 'status', 'latitude', 'longitude', 'image_name'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
