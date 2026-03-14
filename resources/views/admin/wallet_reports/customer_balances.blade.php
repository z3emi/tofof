@extends('admin.layout')

@section('title', __('أرصدة العملاء'))

@section('content')
<h1 class="h3 mb-4">{{ __('أرصدة العملاء الحالية') }}</h1>

<div class="card">
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>{{ __('العميل') }}</th>
                    <th>{{ __('الهاتف') }}</th>
                    <th>{{ __('الرصيد') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                    <tr>
                        <td>{{ $customer->name }}</td>
                        <td>{{ $customer->phone_number }}</td>
                        <td>
                            <span class="fw-bold {{ $customer->balance > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($customer->balance, 2) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">{{ __('لا يوجد عملاء.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
