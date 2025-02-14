<?php

namespace App\Http\Controllers\Other;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResponseResource;
use App\Models\Students\Student;
use App\Models\Transaction\Transactions;

class OtherController extends Controller
{
    public function overview()
    {
        try {
            // Total saldo semua siswa
            $totalBalance = Student::sum('balance');

            // Total siswa terdaftar
            $totalStudents = Student::count();

            // Total siswa yang sudah diterima
            $acceptedStudents = Student::where('allowed', true)->count();

            // Total siswa yang belum diterima
            $pendingStudents = Student::where('allowed', false)->count();

            // Total transaksi
            $totalTransactions = Transactions::count();

            // Total jumlah transaksi debit dan kredit
            $totalDebitTransactions = Transactions::where('type', 'debit')->count();
            $totalCreditTransactions = Transactions::where('type', 'kredit')->count();

            // Total saldo debit dan kredit
            $totalDebitAmount = Transactions::where('type', 'debit')->sum('amount');
            $totalCreditAmount = Transactions::where('type', 'kredit')->sum('amount');

            // Percentage of debit and credit
            $percentageDebit = $totalTransactions > 0 ? round(($totalDebitTransactions / $totalTransactions) * 100, 1) : 0;
            $percentageCredit = $totalTransactions > 0 ? round(($totalCreditTransactions / $totalTransactions) * 100, 1) : 0;

            // Top 10 siswa dengan saldo tertinggi
            $topStudents = Student::orderBy('balance', 'desc')->limit(10)->get(['id', 'name', 'balance', 'email', 'major', 'class', 'photo']);

            // Format data
            $data = [
                'total_balance' => number_format($totalBalance, 0, ',', '.'),
                'total_students' => $totalStudents,
                'accepted_students' => $acceptedStudents,
                'pending_students' => $pendingStudents,
                'total_transactions' => $totalTransactions,
                'total_debit_transactions' => $totalDebitTransactions,
                'total_credit_transactions' => $totalCreditTransactions,
                'total_debit_amount' => number_format($totalDebitAmount, 0, ',', '.'),
                'total_credit_amount' => number_format($totalCreditAmount, 0, ',', '.'),
                'percentage_debit' => $percentageDebit,
                'percentage_credit' => $percentageCredit,
                'top_students' => $topStudents->map(function ($student) {
                    return [
                        'id' => $student->id,
                        'name' => $student->name,
                        'email' => $student->email,
                        'major' => $student->major,
                        'class' => $student->class,
                        'photo' => $student->photo,
                        'balance' => number_format($student->balance, 0, ',', '.')
                    ];
                }),
            ];

            return ResponseResource::success('Dashboard overview retrieved successfully', $data);
        } catch (\Throwable $th) {
            return ResponseResource::error($th->getMessage());
        }
    }
}
