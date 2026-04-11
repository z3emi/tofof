<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Order;
use App\Models\WalletTransaction;
use App\Traits\SendsWhatsAppOtp;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProfileController extends Controller
{
	use SendsWhatsAppOtp;

	public function show(Request $request)
	{
		$user = $request->user();

		return response()->json([
			'success' => true,
			'data' => [
				'id' => $user->id,
				'name' => $user->name,
				'email' => $user->email,
				'phone_number' => $user->phone_number,
				'avatar' => $user->avatar,
				'wallet_balance' => (float) $user->wallet_balance,
				'referral_code' => $user->referral_code,
				'phone_verified_at' => $user->phone_verified_at,
				'banned_at' => $user->banned_at,
			],
		]);
	}

	public function update(Request $request)
	{
		try {
			$validated = $request->validate([
				'name' => 'sometimes|string|max:255',
				'email' => 'sometimes|email|unique:users,email,' . $request->user()->id,
				'phone_number' => 'sometimes|string|unique:users,phone_number,' . $request->user()->id,
				'avatar' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:5120',
			]);

			$user = $request->user();

			if ($request->hasFile('avatar')) {
				if ($user->avatar && Storage::exists($user->avatar)) {
					Storage::delete($user->avatar);
				}
				$validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
			}

			$user->update($validated);

			return response()->json([
				'success' => true,
				'message' => 'تم تحديث الملف الشخصي بنجاح',
				'data' => [
					'id' => $user->id,
					'name' => $user->name,
					'email' => $user->email,
					'phone_number' => $user->phone_number,
					'avatar' => $user->avatar,
					'wallet_balance' => (float) $user->wallet_balance,
					'referral_code' => $user->referral_code,
				],
			]);
		} catch (ValidationException $e) {
			return response()->json([
				'success' => false,
				'message' => 'خطأ في البيانات المدخلة',
				'errors' => $e->errors(),
			], 422);
		} catch (Throwable $e) {
			return response()->json([
				'success' => false,
				'message' => $e->getMessage(),
			], 422);
		}
	}

	public function sendPasswordChangeOtp(Request $request)
	{
		try {
			$user = $request->user();

			if (is_null($user->phone_verified_at)) {
				return response()->json([
					'success' => false,
					'message' => 'رقم الهاتف غير مفعل.',
				], 422);
			}

			$otp = random_int(100000, 999999);

			$user->forceFill([
				'whatsapp_otp' => (string) $otp,
				'whatsapp_otp_expires_at' => Carbon::now()->addMinutes(10),
			])->save();

			$this->sendOtpViaWhatsApp($user->phone_number, $otp);

			return response()->json([
				'success' => true,
				'message' => 'تم إرسال رمز التحقق إلى رقم هاتف الحساب عبر واتساب.',
				'data' => [
					'phone_number' => $user->phone_number,
				],
			]);
		} catch (Throwable $e) {
			return response()->json([
				'success' => false,
				'message' => 'تعذر إرسال رمز التحقق حاليًا.',
			], 500);
		}
	}

	public function changePassword(Request $request)
	{
		try {
			$validated = $request->validate([
				'old_password' => 'required|string',
				'otp' => 'required|digits:6',
				'password' => 'required|string|min:8|confirmed',
			]);

			$user = $request->user();

			if (!Hash::check($validated['old_password'], $user->password)) {
				return response()->json([
					'success' => false,
					'message' => 'كلمة المرور القديمة غير صحيحة.',
				], 422);
			}

			if (!$user->whatsapp_otp || !$user->whatsapp_otp_expires_at) {
				return response()->json([
					'success' => false,
					'message' => 'لا يوجد رمز تحقق صالح. أعد طلب الرمز.',
				], 422);
			}

			if ($user->whatsapp_otp !== $validated['otp'] || Carbon::now()->greaterThan($user->whatsapp_otp_expires_at)) {
				return response()->json([
					'success' => false,
					'message' => 'رمز التحقق غير صحيح أو منتهي الصلاحية.',
				], 422);
			}

			$user->forceFill([
				'password' => Hash::make($validated['password']),
				'whatsapp_otp' => null,
				'whatsapp_otp_expires_at' => null,
			])->save();

			return response()->json([
				'success' => true,
				'message' => 'تم تغيير كلمة المرور بنجاح.',
			]);
		} catch (ValidationException $e) {
			return response()->json([
				'success' => false,
				'message' => 'خطأ في البيانات المدخلة',
				'errors' => $e->errors(),
			], 422);
		} catch (Throwable $e) {
			return response()->json([
				'success' => false,
				'message' => 'تعذر تغيير كلمة المرور حاليًا.',
			], 500);
		}
	}

	public function orders(Request $request)
	{
		try {
			$validated = $request->validate([
				'page' => 'integer|min:1',
				'per_page' => 'integer|min:1|max:50',
				'status' => 'in:pending,processing,shipped,delivered,cancelled,returned',
			]);

			$user = $request->user();
			if (!$user) {
				return response()->json([
					'success' => false,
					'message' => 'Unauthenticated.',
				], 401);
			}

			$perPage = $validated['per_page'] ?? 20;

			$query = $user->orders()
				->withCount('items')
				->latest();

			if (isset($validated['status'])) {
				$query->where('status', $validated['status']);
			}

			$orders = $query->paginate($perPage);

			$items = $orders->getCollection()->map(fn ($order) => [
				'id' => $order->id,
				'total_amount' => (float) $order->total_amount,
				'discount_amount' => (float) $order->discount_amount,
				'shipping_cost' => (float) $order->shipping_cost,
				'payment_method' => $order->payment_method,
				'payment_status' => $order->payment_status,
				'status' => $order->status,
				'items_count' => (int) ($order->items_count ?? 0),
				'created_at' => $order->created_at,
				'is_gift' => $order->is_gift,
			]);

			return response()->json([
				'success' => true,
				'data' => [
					'orders' => $items,
					'total' => $orders->total(),
					'current_page' => $orders->currentPage(),
					'per_page' => $orders->perPage(),
					'last_page' => $orders->lastPage(),
				],
			]);
		} catch (ValidationException $e) {
			return response()->json([
				'success' => false,
				'message' => 'خطأ في البيانات المدخلة',
				'errors' => $e->errors(),
			], 422);
		} catch (Throwable $e) {
			return response()->json([
				'success' => false,
				'message' => 'تعذر جلب الطلبات حاليًا.',
				'error' => config('app.debug') ? $e->getMessage() : null,
			], 500);
		}
	}

	public function showOrder(Request $request, $orderId)
	{
		try {
			$user = $request->user();

			$order = Order::where('id', $orderId)
				->where('user_id', $user->id)
				->with('items.product', 'discountCode')
				->first();

			if (!$order) {
				return response()->json([
					'success' => false,
					'message' => 'الطلب غير موجود',
				], 404);
			}

			$walletAmount = WalletTransaction::where('order_id', $order->id)
				->where('type', 'debit')
				->sum('amount');

			$items = $order->items->map(fn ($item) => [
				'id' => $item->id,
				'product_id' => $item->product_id,
				'product_name' => optional($item->product)->name_translated ?? 'منتج محذوف',
				'quantity' => $item->quantity,
				'price' => (float) $item->price,
				'total' => (float) ($item->price * $item->quantity),
				'cost' => $item->cost,
				'option_selections' => json_decode($item->option_selections),
			]);

			return response()->json([
				'success' => true,
				'data' => [
					'id' => $order->id,
					'total_amount' => (float) $order->total_amount,
					'discount_amount' => (float) $order->discount_amount,
					'shipping_cost' => (float) $order->shipping_cost,
					'subtotal' => (float) ($order->total_amount + $order->discount_amount - $order->shipping_cost),
					'wallet_paid' => (float) $walletAmount,
					'remaining_amount' => (float) ($order->total_amount - $walletAmount),
					'payment_method' => $order->payment_method,
					'payment_status' => $order->payment_status,
					'status' => $order->status,
					'discount_code' => $order->discountCode?->code,
					'governorate' => $order->governorate,
					'city' => $order->city,
					'address_details' => $order->address_details,
					'nearest_landmark' => $order->nearest_landmark,
					'is_gift' => $order->is_gift,
					'gift_recipient_name' => $order->gift_recipient_name,
					'gift_recipient_phone' => $order->gift_recipient_phone,
					'gift_message' => $order->gift_message,
					'items' => $items,
					'created_at' => $order->created_at,
					'updated_at' => $order->updated_at,
				],
			]);
		} catch (Throwable $e) {
			return response()->json([
				'success' => false,
				'message' => $e->getMessage(),
			], 422);
		}
	}

	public function addresses(Request $request)
	{
		try {
			$user = $request->user();

			$addresses = $user->addresses()
				->orderBy('is_default', 'desc')
				->get()
				->map(fn ($addr) => [
					'id' => $addr->id,
					'governorate' => $addr->governorate,
					'city' => $addr->city,
					'address_details' => $addr->address_details,
					'nearest_landmark' => $addr->nearest_landmark,
					'latitude' => $addr->latitude,
					'longitude' => $addr->longitude,
					'is_default' => $addr->is_default,
				]);

			return response()->json([
				'success' => true,
				'data' => [
					'addresses' => $addresses,
					'count' => $addresses->count(),
				],
			]);
		} catch (Throwable $e) {
			return response()->json([
				'success' => false,
				'message' => $e->getMessage(),
			], 422);
		}
	}

	public function storeAddress(Request $request)
	{
		try {
			$validated = $request->validate([
				'governorate' => 'required|string',
				'city' => 'required|string',
				'address_details' => 'required|string',
				'nearest_landmark' => 'nullable|string',
				'latitude' => 'nullable|numeric',
				'longitude' => 'nullable|numeric',
				'is_default' => 'boolean',
			]);

			$user = $request->user();

			if ($user->addresses()->count() >= 5) {
				return response()->json([
					'success' => false,
					'message' => 'لا يمكنك إضافة أكثر من 5 عناوين',
				], 422);
			}

			if ($validated['is_default'] ?? false) {
				$user->addresses()->update(['is_default' => false]);
			}

			$address = $user->addresses()->create($validated);

			return response()->json([
				'success' => true,
				'message' => 'تم إضافة العنوان بنجاح',
				'data' => [
					'id' => $address->id,
					'governorate' => $address->governorate,
					'city' => $address->city,
					'address_details' => $address->address_details,
					'nearest_landmark' => $address->nearest_landmark,
					'is_default' => $address->is_default,
				],
			], 201);
		} catch (ValidationException $e) {
			return response()->json([
				'success' => false,
				'message' => 'خطأ في البيانات المدخلة',
				'errors' => $e->errors(),
			], 422);
		}
	}

	public function updateAddress(Request $request, $addressId)
	{
		try {
			$validated = $request->validate([
				'governorate' => 'sometimes|string',
				'city' => 'sometimes|string',
				'address_details' => 'sometimes|string',
				'nearest_landmark' => 'nullable|string',
				'latitude' => 'nullable|numeric',
				'longitude' => 'nullable|numeric',
				'is_default' => 'boolean',
			]);

			$user = $request->user();
			$address = Address::where('id', $addressId)
				->where('user_id', $user->id)
				->first();

			if (!$address) {
				return response()->json([
					'success' => false,
					'message' => 'العنوان غير موجود',
				], 404);
			}

			if ($validated['is_default'] ?? false) {
				$user->addresses()->update(['is_default' => false]);
			}

			$address->update($validated);

			return response()->json([
				'success' => true,
				'message' => 'تم تحديث العنوان بنجاح',
				'data' => [
					'id' => $address->id,
					'governorate' => $address->governorate,
					'city' => $address->city,
					'address_details' => $address->address_details,
					'nearest_landmark' => $address->nearest_landmark,
					'is_default' => $address->is_default,
				],
			]);
		} catch (ValidationException $e) {
			return response()->json([
				'success' => false,
				'message' => 'خطأ في البيانات المدخلة',
				'errors' => $e->errors(),
			], 422);
		}
	}

	public function destroyAddress(Request $request, $addressId)
	{
		try {
			$user = $request->user();
			$address = Address::where('id', $addressId)
				->where('user_id', $user->id)
				->first();

			if (!$address) {
				return response()->json([
					'success' => false,
					'message' => 'العنوان غير موجود',
				], 404);
			}

			$address->delete();

			return response()->json([
				'success' => true,
				'message' => 'تم حذف العنوان بنجاح',
			]);
		} catch (Throwable $e) {
			return response()->json([
				'success' => false,
				'message' => $e->getMessage(),
			], 422);
		}
	}

	public function wallet(Request $request)
	{
		try {
			$user = $request->user();

			$transactions = $user->walletTransactions()->latest()->paginate(20);

			$items = $transactions->getCollection()->map(fn ($trans) => [
				'id' => $trans->id,
				'type' => $trans->type,
				'amount' => (float) $trans->amount,
				'description' => $trans->description,
				'balance_after' => (float) $trans->balance_after,
				'order_id' => $trans->order_id,
				'created_at' => $trans->created_at,
			]);

			return response()->json([
				'success' => true,
				'data' => [
					'balance' => (float) $user->wallet_balance,
					'transactions' => $items,
					'total' => $transactions->total(),
					'current_page' => $transactions->currentPage(),
					'per_page' => $transactions->perPage(),
					'last_page' => $transactions->lastPage(),
				],
			]);
		} catch (Throwable $e) {
			return response()->json([
				'success' => false,
				'message' => $e->getMessage(),
			], 422);
		}
	}

	public function discounts(Request $request)
	{
		try {
			$discounts = \App\Models\DiscountCode::where('is_active', true)
				->where(function ($query) {
					$query->whereNull('expires_at')
						->orWhere('expires_at', '>', now());
				})
				->with('products', 'categories')
				->paginate(20);

			$items = $discounts->getCollection()->map(fn ($discount) => [
				'id' => $discount->id,
				'code' => $discount->code,
				'type' => $discount->type,
				'value' => (float) $discount->value,
				'max_discount_amount' => $discount->max_discount_amount ? (float) $discount->max_discount_amount : null,
				'expires_at' => $discount->expires_at,
				'description' => $discount->description,
			]);

			return response()->json([
				'success' => true,
				'data' => [
					'discounts' => $items,
					'total' => $discounts->total(),
					'current_page' => $discounts->currentPage(),
					'per_page' => $discounts->perPage(),
					'last_page' => $discounts->lastPage(),
				],
			]);
		} catch (Throwable $e) {
			return response()->json([
				'success' => false,
				'message' => $e->getMessage(),
			], 422);
		}
	}

	public function notifications(Request $request)
	{
		try {
			$user = $request->user();

			$notifications = \App\Models\Notification::where('user_id', $user->id)
				->latest()
				->paginate(20);

			$items = $notifications->getCollection()->map(fn ($notif) => [
				'id' => $notif->id,
				'title' => $notif->title,
				'body' => $notif->body,
				'image' => $notif->image,
				'is_read' => $notif->is_read,
				'created_at' => $notif->created_at,
			]);

			return response()->json([
				'success' => true,
				'data' => [
					'notifications' => $items,
					'total' => $notifications->total(),
					'current_page' => $notifications->currentPage(),
					'per_page' => $notifications->perPage(),
					'last_page' => $notifications->lastPage(),
				],
			]);
		} catch (Throwable $e) {
			return response()->json([
				'success' => false,
				'message' => $e->getMessage(),
			], 422);
		}
	}
}

