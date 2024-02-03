<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Traits\HasPermissions;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory,HasRoles, Notifiable , HasPermissions;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', 'email', 'password', 'employee_id', 'department_id', 'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isSubAdmin(): bool
    {
        return $this->hasRole('sub-admin');
    }

    public function isEmployee(): bool
    {
        return $this->hasRole('employee');
    }
    public function personalDetails()
    {
        return $this->hasOne(PersonalDetail::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }
    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }
}
