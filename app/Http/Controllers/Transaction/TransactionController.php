<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResponseResource;
use App\Models\Students\Student;
use App\Models\Transaction\Transactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Handle transaction request
     * 
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\ResponseResource
     */
    public function handleTransaction(Request $request)
    {
        try {
            // Get token from header
            $token = $request->bearerToken();

            if (!$token) {
                return ResponseResource::error('Token tidak ditemukan', 401);
            }

            // Get admin from token
            $admin = auth('sanctum')->user();

            if (!$admin) {
                return ResponseResource::error('Invalid token', 401);
            }

            // Validate request
            $request->validate([
                'student_id' => 'required|exists:students,id',
                'type'       => 'required|in:debit,kredit',
                'amount'     => 'required|numeric|min:1',
            ]);

            // Get student
            $student = Student::findOrFail($request->student_id);

            // Check allowed status
            if (!$student->allowed) {
                return ResponseResource::error('Siswa belum disetujui', 400);
            }

            $type = $request->type;
            $amount = $request->amount;

            // Check balance
            if ($type === 'kredit' && $student->balance < $amount) {
                return ResponseResource::error('Saldo tidak mencukupi...', 400);
            }

            // Start transaction
            $response = DB::transaction(function () use ($student, $admin, $type, $amount) {

                // Update balance
                if ($type === 'debit') {
                    $student->balance += $amount;
                } elseif ($type === 'kredit') {
                    $student->balance -= $amount;
                }

                $student->save();

                // Create transaction record    
                $transaction = Transactions::create([
                    'student_id' => $student->id,
                    'admin_id'   => $admin->id,
                    'type'       => $type,
                    'amount'     => $amount,
                ]);

                return response()->json([
                    'status' => 200,
                    'message' => ucfirst($type) . ' Success Transaction',
                    'result' => [
                        'id' => $transaction->id,
                        'student' => [
                            'id' => $student->id,
                            'name' => $student->name,
                            'balance' => number_format($student->balance, 0, ',', '.'),
                        ],
                        'admin' => [
                            'id' => $admin->id,
                            'name' => $admin->name,
                            'role' => $admin->role->name,
                        ],
                        'type' => $type,
                        'amount' => number_format($amount, 0, ',', '.'),
                        'created_at' => $transaction->created_at->toIso8601String(),
                        'updated_at' => $transaction->updated_at->toIso8601String(),
                    ]
                ]);
            });

            return $response;
        } catch (\Throwable $th) {
            return ResponseResource::error($th->getMessage());
        }
    }
}
