@extends('admin.layout')

@section('title', __('عهد المندوبين'))

@section('content')
<h1 class="h3 mb-4">{{ __('أرصدة عهد المندوبين') }}</h1>

<div class="card">
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>{{ __('المندوب') }}</th>
                    <th>{{ __('رقم الهاتف') }}</th>
                    <th>{{ __('الرصيد النقدي') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($managers as $manager)
                    <tr>
                        <td>{{ $manager->name }}</td>
                        <td>{{ $manager->phone_number }}</td>
                        <td>
                            <span class="fw-bold {{ $manager->cash_on_hand > 0 ? 'text-warning' : 'text-success' }}">
                                {{ number_format($manager->cash_on_hand, 2) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">{{ __('لا يوجد مندوبون مسجلون.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
