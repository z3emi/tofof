@extends('layouts.app') {{-- Or your main frontend layout --}}
@section('title', __('maintenance.title'))

@section('content')
<div class="container mx-auto text-center py-20">
    <i class="bi bi-gear-wide-connected text-8xl text-brand-primary"></i>
    <h1 class="text-4xl font-bold text-brand-dark mt-4">{{ __('maintenance.heading') }}</h1>
    <p class="text-lg text-gray-600 mt-2">
        {{ __('maintenance.line_1') }}
        <br>
        {{ __('maintenance.line_2') }}
    </p>
</div>
@endsection
