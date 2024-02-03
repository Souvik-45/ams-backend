<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalDetail extends Model
{
    protected $fillable = ['user_id' ,'name','email','address', 'mobileno']; // Add personal details columns
    protected $table = 'personal_details';

    // Add relationships and methods as needed

    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }
}
