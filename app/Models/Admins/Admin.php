<?php

namespace App\Models\Admins;

use App\Models\Students\Student;
use App\Models\Transaction\Transactions;
use App\Models\Admins\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'admins';
    protected $fillable = ['name', 'email', 'password', 'role_id', 'photo'];

    // Relationship ke Role
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // Relationship ke Transactions
    public function transactions()
    {
        return $this->hasMany(Transactions::class);
    }

    // Relationship ke Students
    public function students()
    {
        return $this->hasMany(Student::class);
    }
}

