<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResponseResource;
use App\Models\Students\Student;
use App\Models\Transaction\Transactions;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{

    /**
     * Menggambil semua transaksi atau berdasarkan type
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\ResponseResource
     */
    public function index(Request $request): ResponseResource
    {
        try {
            $query = Transactions::with(['student', 'admin']);

            // Filter berdasarkan type jika ada
            if ($request->has('type') && in_array($request->type, ['debit', 'kredit'])) {
                $query->where('type', $request->type);
            }

            // Ambil semua transaksi terbaru
            $transactions = $query->latest()->get();


            $formattedTransactions = $transactions->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'student' => [
                        'id' => $transaction->student->id,
                        'name' => $transaction->student->name,
                        'balance' => $transaction->student->balance,
                        'class' => $transaction->student->class,
                        'major' => $transaction->student->major,
                        'email' => $transaction->student->email
                    ],
                    'admin' => [
                        'id' => $transaction->admin->id,
                        'name' => $transaction->admin->name,
                        'role' => $transaction->admin->role->name,
                    ],
                    'type' => $transaction->type,
                    'amount' => number_format($transaction->amount, 0, ',', '.'),
                    // 'amount' => $transaction->amount,
                    'created_at' => $transaction->created_at->format('Y-m-d H:i:s'),
                ];
            });

            if ($transactions->isEmpty()) {
                $message = $request->has('type') ? "Data transaksi type {$request->type} tidak ditemukan" : "Data transaksi tidak ditemukan";
                return ResponseResource::notFound($message);
            }

            $message = $request->type ? "Data transaksi type $request->type ditemukan" : "Data transaksi semua ditemukan";
            return ResponseResource::success($message, $formattedTransactions);
        } catch (\Throwable $th) {
            return ResponseResource::error($th->getMessage());
        }
    }

    /**
     * Mengambil semua transaksi berdasarkan ID pengguna (admin atau student).
     *
     * @param int $userId
     * @param string $role ('admin' atau 'student')
     * @return \App\Http\Resources\ResponseResource
     */
    public function indexByUser(Request $request, $userId, $role): ResponseResource
    {
        try {
            if (!in_array($role, ['admin', 'student'])) {
                return ResponseResource::error("Peran harus 'admin' atau 'student'");
            }

            // Query berdasarkan peran
            $query = Transactions::with(['student', 'admin']);

            if ($role === 'admin') {
                $query->where('admin_id', $userId);
            } elseif ($role === 'student') {
                $query->where('student_id', $userId);
            }

            // Filter berdasarkan type jika ada
            if ($request->has('type') && in_array($request->type, ['debit', 'kredit'])) {
                $query->where('type', $request->type);
            }

            // Ambil transaksi terbaru
            $transactions = $query->latest()->get();

            // Format data
            $formattedTransactions = $transactions->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'student' => [
                        'id' => $transaction->student->id,
                        'name' => $transaction->student->name,
                        'balance' => number_format($transaction->student->balance, 0, ',', '.'),
                        'class' => $transaction->student->class,
                        'major' => $transaction->student->major,
                        'email' => $transaction->student->email
                    ],
                    'admin' => [
                        'id' => $transaction->admin->id,
                        'name' => $transaction->admin->name,
                        'role' => $transaction->admin->role->name,
                    ],
                    'type' => $transaction->type,
                    'amount' => number_format($transaction->amount, 0, ',', '.'),
                    'created_at' => $transaction->created_at->format('Y-m-d H:i:s'),
                ];
            });

            if ($transactions->isEmpty()) {
                $message = $request->has('type')
                    ? "Data transaksi type {$request->type} untuk {$role} dengan ID $userId tidak ditemukan"
                    : "Data transaksi untuk {$role} dengan ID $userId tidak ditemukan";
                return ResponseResource::notFound($message);
            }

            $message = $request->has('type')
                ? "Data transaksi type {$request->type} untuk {$role} dengan ID $userId ditemukan"
                : "Data transaksi untuk {$role} dengan ID $userId ditemukan";

            return ResponseResource::success($message, $formattedTransactions);
        } catch (\Throwable $th) {
            return ResponseResource::error($th->getMessage());
        }
    }

    const MAX_TRANSACTION = 10000000;

    /**
     * Handle untuk membuat transaksi baru .
     *
     * Fungsi ini bertanggung jawab untuk memvalidasi data permintaan yang masuk,
     * Memastikan validitas dan kecukupan keseimbangan siswa untuk meminta transaksi, dan membuat catatan transaksi baru. Itu mendukung
     * Jenis transaksi 'debit' dan 'kredit' dan menyesuaikan keseimbangan siswa sesuai. Fungsi mengembalikan respons yang berhasil dengan transaksi
     * Detail Jika operasi berhasil, atau respons kesalahan jika ada validasi atau kesalahan pemrosesan terjadi.
     *
     */
    public function store(Request $request)
    {
        // Validasi request
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'type' => 'required|in:debit,kredit',
            'amount' => 'required|numeric|min:1|max:' . self::MAX_TRANSACTION,
        ]);

        if ($request->amount > self::MAX_TRANSACTION) {
            return ResponseResource::error('Jumlah melebihi batas maksimal transaksi (Rp. ' . number_format(self::MAX_TRANSACTION, 0, ',', '.') . ')');
        }

        try {
            DB::beginTransaction();

            $student = Student::findOrFail($request->student_id);

            if (!$student) {
                return ResponseResource::notFound();
            }

            if (!$student->allowed) {
                return ResponseResource::error('Siswa belum diizinkan bertransaksi');
            }

            // Cek saldo jika kredit
            if ($request->type === 'kredit') {
                if ($student->balance < $request->amount) {
                    return ResponseResource::error('Saldo tidak mencukupi');
                }
            }

            $transaction = Transactions::create([
                'student_id' => $request->student_id,
                'admin_id' => Auth::user()->id,
                'type' => $request->type,
                'amount' => $request->amount
            ]);

            if ($request->type === 'debit') {
                $student->balance += $request->amount;
            } else {
                $student->balance -= $request->amount;
            }

            $student->save();

            DB::commit();

            $formattedTransaction = [
                'id' => $transaction->id,
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'balance' => number_format($student->balance, 0, ',', '.'),
                    'class' => $student->class,
                    'major' => $student->major
                ],
                'admin' => [
                    'id' => Auth::user()->id,
                    'name' => Auth::user()->name,
                    'role' => Auth::user()->role->name,
                ],
                'type' => $request->type,
                'amount' => $request->amount,
                'created_at' => $transaction->created_at->toIso8601String(),
            ];

            return ResponseResource::success('Transaksi berhasil', $formattedTransaction);
        } catch (\Exception $e) {
            DB::rollback();
            return ResponseResource::error($e->getMessage());
        }
    }


    /**
     * Mengambil data transaksi harian dalam satu minggu.
     * 
     * @return ResponseResource
     */
    public function getWeeklyTransactions()
    {
        try {
            $startDate = Carbon::now()->startOfWeek();
            $endDate = Carbon::now()->endOfWeek();

            $transactions = Transactions::whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('DATE(created_at) as date, 
                         SUM(CASE WHEN type = "kredit" THEN amount ELSE 0 END) as total_credit, 
                         SUM(CASE WHEN type = "debit" THEN amount ELSE 0 END) as total_debit, 
                         COUNT(CASE WHEN type = "kredit" THEN 1 END) as total_credit_count, 
                         COUNT(CASE WHEN type = "debit" THEN 1 END) as total_debit_count, 
                         COUNT(*) as total_transactions')
                ->groupBy('date')
                ->get();

            $formattedTransactions = $transactions->map(function ($transaction) {
                return [
                    'date' => $transaction->date,
                    'total_credit' => number_format($transaction->total_credit, 0, ',', '.'),
                    'total_debit' => number_format($transaction->total_debit, 0, ',', '.'),
                    'total_transactions' => $transaction->total_transactions,
                    'total_credit_count' => $transaction->total_credit_count,
                    'total_debit_count' => $transaction->total_debit_count
                ];
            });

            if ($transactions->isEmpty()) {
                return ResponseResource::notFound();
            }

            return ResponseResource::success('Data Berhasil Ditemukan', $formattedTransactions);
        } catch (\Throwable $th) {
            return ResponseResource::error($th->getMessage());
        }
    }
}
