<?php

namespace App\Models\Transaction;

use App\Models\Admins\Admin;
use App\Models\Students\Student;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    protected $fillable = ['student_id', 'admin_id', 'type', 'amount'];

    // Relationship to Table Student
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    // Relationship to Table Admin
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    // Custom Attribute Balance
    protected function amount(): Attribute
    {
        return Attribute::make(
            get: fn($value) => number_format($value, 0, ',', '.')
        );
    }
}
