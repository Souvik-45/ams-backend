<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Attendance extends Model
{
    protected $fillable = ['user_id', 'date', 'time_in', 'time_out', 'location', 'status', 'image_url'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
