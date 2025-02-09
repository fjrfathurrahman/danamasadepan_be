<?php

namespace App\Models\Admins;

use App\Models\Students\Student;
use App\Models\Transaction\Transactions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Model
{
    use HasApiTokens, HasFactory;

    protected $table = 'admins';
    protected $fillable = ['name', 'email', 'password', 'role_id', 'photo'];


    // Relationship to Table Role
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // Relationship to Table Transaction
    public function transactions()
    {
        return $this->hasMany(Transactions::class);
    }

    // Relationship to Table Student
    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
