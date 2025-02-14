<?php

namespace App\Models\Students;

use App\Models\Admins\Admin;
use App\Models\Transaction\Transactions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Student extends Model
{
    use HasApiTokens, HasFactory;

    protected $table = 'students';
    protected $fillable = ['name', 'email', 'password', 'gender', 'class', 'major', 'phone', 'address', 'photo', 'balance', 'allowed'];

    // Relationship ke Admin (jika Student memiliki Admin)
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    // Relationship ke Transactions (1 Student bisa punya banyak transaksi)
    public function transactions()
    {
        return $this->hasMany(Transactions::class);
    }

    // Mengubah nilai balance menjadi format rupiah
    // public function getBalanceAttribute($value)
    // {
    //     return number_format($value, 0, ',', '.');
    // }

    /**
     * Mengubah nilai allowed
     */
    protected $casts = [
        'allowed' => 'boolean',  // Mengubah nilai 0/1 menjadi false/true
        // 'balance' => 'decimal:2',
    ];
}
