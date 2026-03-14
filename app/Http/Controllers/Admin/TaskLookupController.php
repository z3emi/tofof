<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Manager;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskLookupController extends Controller
{
    public function customers(Request $request): JsonResponse
    {
        /** @var Manager|null $manager */
        $manager = $request->user('admin');
        $this->authorizeLookup($manager);

        $search = trim((string) $request->input('q', ''));

        $customers = Customer::query()
            ->select(['id', 'name', 'display_name', 'phone', 'phone_secondary'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('display_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('phone_secondary', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $results = $customers->map(function (Customer $customer) {
            $name = $customer->display_name ?: $customer->name ?: 'عميل';
            $phones = collect([$customer->phone, $customer->phone_secondary])
                ->filter()
                ->unique()
                ->implode(' / ');

            $labelParts = [$name . ' #' . $customer->id];
            if ($phones !== '') {
                $labelParts[] = $phones;
            }

            return [
                'id' => $customer->id,
                'text' => implode(' — ', $labelParts),
            ];
        });

        return response()->json($results);
    }

    public function orders(Request $request): JsonResponse
    {
        /** @var Manager|null $manager */
        $manager = $request->user('admin');
        $this->authorizeLookup($manager);

        $search = trim((string) $request->input('q', ''));

        $orders = Order::query()
            ->with(['customer:id,name,display_name'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    if (is_numeric($search)) {
                        $q->orWhere('id', (int) $search);
                    }

                    $q->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('display_name', 'like', "%{$search}%");
                    });
                });
            })
            ->latest('created_at')
            ->limit(20)
            ->get();

        $results = $orders->map(function (Order $order) {
            $customerName = $order->customer?->display_name ?: $order->customer?->name;
            $labelParts = ['طلب #' . $order->id];

            if ($customerName) {
                $labelParts[] = $customerName;
            }

            if (!is_null($order->total_amount)) {
                $labelParts[] = number_format((float) $order->total_amount, 2) . ' د.ع';
            }

            return [
                'id' => $order->id,
                'text' => implode(' — ', $labelParts),
            ];
        });

        return response()->json($results);
    }

    private function authorizeLookup(?Manager $manager): void
    {
        if (!$manager) {
            abort(403);
        }

        if (!($manager->can('create-tasks') || $manager->can('edit-tasks') || $manager->can('assign-tasks'))) {
            abort(403);
        }
    }
}
