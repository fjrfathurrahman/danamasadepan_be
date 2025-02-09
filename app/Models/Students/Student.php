<?php

namespace App\Models\Students;

use App\Models\Admins\Admin;
use App\Models\Transaction\Transactions;
use Illuminate\Database\Eloquent\Casts\Attribute;
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

    // Relationship to Table Transaction
    public function transactions()
    {
        return $this->hasMany(Transactions::class);
    }

    /**
     * Get formatted balance
     * 
     * @return string
     */
    public function getFormattedBalanceAttribute()
    {
        return number_format($this->balance, 0, ',', '.');
    }


    /**
     * Mengubah nilai allowed
     */
    protected $casts = [
        'allowed' => 'boolean',  // Mengubah nilai 0/1 menjadi false/true
        'balance' => 'decimal:2', // Mengubah balance menjadi decimal untuk akurasi
    ];
}
