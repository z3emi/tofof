<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // يفترض وجود الحقول/العلاقات التالية:
        // users.wallet_balance (decimal)
        // $user->walletTransactions() => hasMany WalletTransaction
        $transactions = $user->walletTransactions()
            ->latest()
            ->paginate(12);

        $credits = (clone $transactions->getCollection())->where('type','credit')->sum('amount');
        $debits  = (clone $transactions->getCollection())->where('type','debit')->sum('amount');

        return view('frontend.wallet.index', [
            'user'         => $user,
            'transactions' => $transactions,
            'credits'      => $credits,
            'debits'       => $debits,
        ]);
    }
}
