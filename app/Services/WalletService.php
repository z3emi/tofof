<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\WalletTransaction;
use App\Support\RepairsPrimaryKeyAutoIncrement;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class WalletService
{
    /**
     * إضافة رصيد لمستخدم مع تسجيل العملية.
     */
    public static function credit(User $user, float $amount, string $description): void
    {
        if ($amount <= 0) {
            return;
        }

        try {
            self::creditWithoutRepair($user, $amount, $description);
        } catch (QueryException $exception) {
            if (! RepairsPrimaryKeyAutoIncrement::isMissingAutoIncrementError($exception, 'wallet_transactions')) {
                throw $exception;
            }

            // Repair runs outside transactions because ALTER TABLE causes implicit commit in MySQL.
            RepairsPrimaryKeyAutoIncrement::ensure('wallet_transactions');

            self::creditWithoutRepair($user, $amount, $description);
        }
    }

    /**
     * سحب رصيد من مستخدم مع تسجيل العملية.
     */
    public static function debit(User $user, float $amount, string $description): void
    {
        if ($amount <= 0 || (float)$user->wallet_balance < $amount) {
            throw new \Exception('الرصيد في المحفظة غير كافٍ.');
        }

        try {
            self::debitWithoutRepair($user, $amount, $description);
        } catch (QueryException $exception) {
            if (! RepairsPrimaryKeyAutoIncrement::isMissingAutoIncrementError($exception, 'wallet_transactions')) {
                throw $exception;
            }

            // Repair runs outside transactions because ALTER TABLE causes implicit commit in MySQL.
            RepairsPrimaryKeyAutoIncrement::ensure('wallet_transactions');

            self::debitWithoutRepair($user, $amount, $description);
        }
    }

    /**
     * منح مكافأة تسجيل للمستخدم الجديد (المدعو).
     */
    public static function creditRegistrationBonus(User $newUser): void
    {
        // تأكد أن المستخدم الجديد لديه شخص قام بدعوته ولم يحصل على المكافأة من قبل
        if ($newUser->referred_by && !$newUser->referral_reward_claimed) {
            $bonusAmount = 1000; // مبلغ المكافأة
            $referrer = User::find($newUser->referred_by);
            if($referrer) {
                self::credit($newUser, $bonusAmount, 'مكافأة تسجيل عن طريق دعوة من ' . $referrer->name);
            }
        }
    }

    /**
     * منح مكافأة للداعي عند اكتمال أول طلب للمدعو.
     */
    public static function creditReferralBonusForFirstOrder(Order $order): void
    {
        $customer = $order->user;
        
        // تحقق: هل المستخدم مدعو؟ وهل حصل الداعي على مكافأته من قبل؟
        if (!$customer || !$customer->referred_by || $customer->referrer_bonus_awarded) {
            return;
        }
        
        // ✅ [تصحيح] التأكد من أن هذا هو أول طلب مكتمل بالفعل لهذا المستخدم
        $deliveredOrderCount = Order::where('user_id', $customer->id)
            ->where('status', 'delivered')
            ->count();
            
        // يتم استدعاء هذه الدالة بعد تحديث الطلب، لذلك يجب أن يكون العدد 1 بالضبط
        if ($deliveredOrderCount !== 1) {
            return;
        }

        $referrer = User::find($customer->referred_by);
        if ($referrer) {
            $bonusAmount = 1000;
            self::credit($referrer, $bonusAmount, 'مكافأة دعوة لاكتمال أول طلب للمستخدم ' . $customer->name);
            
            // ✅ [إضافة هامة] تحديث الحالة لمنع منح المكافأة مرة أخرى أبداً لهذا المستخدم
            $customer->update(['referrer_bonus_awarded' => true]);
        }
    }

    private static function creditWithoutRepair(User $user, float $amount, string $description): void
    {
        DB::transaction(function () use ($user, $amount, $description) {
            $newBalance = (float)$user->wallet_balance + $amount;

            WalletTransaction::create([
                'user_id'       => $user->id,
                'type'          => 'credit',
                'amount'        => $amount,
                'description'   => $description,
                'balance_after' => $newBalance,
            ]);

            $user->wallet_balance = $newBalance;
            $user->save();
        });
    }

    private static function debitWithoutRepair(User $user, float $amount, string $description): void
    {
        DB::transaction(function () use ($user, $amount, $description) {
            $newBalance = (float)$user->wallet_balance - $amount;

            WalletTransaction::create([
                'user_id'       => $user->id,
                'type'          => 'debit',
                'amount'        => $amount,
                'description'   => $description,
                'balance_after' => $newBalance,
            ]);

            $user->wallet_balance = $newBalance;
            $user->save();
        });
    }
}