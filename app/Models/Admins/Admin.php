<?php

namespace App\Models\Admins;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $table = 'admins';
    protected $fillable = ['name', 'email', 'password'];


    // Relationship to Table Role
    public function Role()
    {
        return $this->hasMany(Role::class);
    }
}
