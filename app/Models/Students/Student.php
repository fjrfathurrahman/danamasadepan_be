<?php

namespace App\Models\Students;

use App\Models\Admins\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Student extends Model
{
    use HasApiTokens, HasFactory;

    protected $table = 'students';
    protected $fillable = ['name', 'email', 'password', 'gender', 'class', 'major', 'phone', 'address', 'photo', 'balance', 'allowed']; 


    // Relationship to Table Admin
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
