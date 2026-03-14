<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    /**
     * تنفيذ إضافة رصيد (POST: admin/wallet/{user}/deposit)
     */
    public function deposit(Request $request, User $user)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'note'   => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($user, $data) {
            $newBalance = (float) ($user->wallet_balance ?? 0) + (float) $data['amount'];

            WalletTransaction::create([
                'user_id'       => $user->id,
                'type'          => 'credit', // إيداع
                'amount'        => $data['amount'],
                'description'   => $data['note'] ?? 'إضافة رصيد (لوحة تحكم)',
                'balance_after' => $newBalance,
            ]);

            $user->wallet_balance = $newBalance;
            $user->save();
        });

        return back()->with('success', 'تم إضافة الرصيد بنجاح');
    }

    /**
     * تنفيذ سحب رصيد (POST: admin/wallet/{user}/withdraw)
     */
    public function withdraw(Request $request, User $user)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'note'   => ['nullable', 'string', 'max:255'],
        ]);

        if ((float) ($user->wallet_balance ?? 0) < (float) $data['amount']) {
            return back()->with('error', 'الرصيد غير كافٍ لعملية السحب');
        }

        DB::transaction(function () use ($user, $data) {
            $newBalance = (float) ($user->wallet_balance ?? 0) - (float) $data['amount'];

            WalletTransaction::create([
                'user_id'       => $user->id,
                'type'          => 'debit', // سحب
                'amount'        => $data['amount'],
                'description'   => $data['note'] ?? 'سحب رصيد (لوحة تحكم)',
                'balance_after' => $newBalance,
            ]);

            $user->wallet_balance = $newBalance;
            $user->save();
        });

        return back()->with('success', 'تم سحب الرصيد بنجاح');
    }
}
