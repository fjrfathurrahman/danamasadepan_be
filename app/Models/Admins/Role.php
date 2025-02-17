<?php

namespace App\Models\Admins;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';
    protected $fillable = ['name'];

    // Relationship to Table Admin
    public function admin()
    {
        return $this->hasMany(Admin::class);
    }
}
