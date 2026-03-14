<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'rating'  => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'], // تعليق اختياري
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => 'اختر تقييمًا من 1 إلى 5.',
            'rating.integer'  => 'التقييم يجب أن يكون رقمًا صحيحًا.',
            'rating.min'      => 'أقل تقييم هو 1.',
            'rating.max'      => 'أعلى تقييم هو 5.',
        ];
    }
}
