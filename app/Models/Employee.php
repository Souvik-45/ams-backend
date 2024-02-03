<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = ['employee_id', 'user_id' ,'department_id','employee_name', 'department_name', 'job_role'];
    protected $table = 'employee';


    // Add other relationships and methods as needed
}
