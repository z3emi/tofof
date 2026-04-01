@extends('layouts.app')

@section('title', __('checkout.title'))

@push('styles')
    <style>
        /* ألوان الأزرار في الوضع الفاتح */
        .bg-brand-primary,
        .bg-brand-dark {
            background-color: #6d0e16 !important;
            color: #fff !important;
        }
        .hover\:bg-brand-primary:hover,
        .hover\:bg-brand-dark:hover {
            background-color: #500a10 !important;
            color: #fff !important;
        }
        .text-brand-primary {
            color: #6d0e16 !important;
        }
        
        /* تحسين الفوكس والتشيك بوكس */
        input:focus, select:focus, textarea:focus {
            border-color: #6d0e16 !important;
            --tw-ring-color: #6d0e16 !important;
        }
        input[type="checkbox"]:checked,
        input[type="radio"]:checked {
            background-color: #6d0e16 !important;
            border-color: #6d0e16 !important;
        }

        .step-badge {
            width: 2.5rem; height: 2.5rem; border-radius: 9999px;
            display: inline-flex; align-items: center; justify-content: center;
            font-weight: bold; color: white;
            box-shadow: 0 4px 10px rgba(109, 14, 22, 0.2);
        }
        .address-card.selected {
            border-color: #6d0e16;
            box-shadow: 0 0 0 2px rgba(109,14,22,.15);
            background-color: rgba(109, 14, 22, 0.02);
        }

        /* ====== Dark Mode Identity Fixes (Premium Dark) ====== */
        /* صناديق بيضاء تصبح أسطح داكنة */
        .dark .bg-white { background-color: #111111 !important; }
        .dark .bg-gray-50,
        .dark .bg-gray-50\/50 { background-color: #0a0a0a !important; }

        /* حدود أخف بالداكن */
        .dark .border,
        .dark .border-gray-200,
        .dark .border-gray-300 { border-color: #1f1f1f !important; }

        /* نصوص */
        .dark .text-brand-text,
        .dark .text-gray-800 { color: #f3f4f6 !important; }
        .dark .text-gray-700 { color: #e5e7eb !important; }
        .dark .text-gray-600 { color: #9ca3af !important; }
        .dark .text-gray-500 { color: #888888 !important; }

        /* بطاقات العناوين عند التحديد تحافظ على لون الهوية */
        .dark .address-card.selected {
            border-color: #6d0e16 !important;
            box-shadow: 0 0 0 2px rgba(109,14,22,.3) !important;
            background-color: rgba(109, 14, 22, 0.1) !important;
        }

        /* بطاقات الأقسام */
        .dark .shadow-sm { box-shadow: 0 10px 30px rgba(0,0,0,.5) !important; }

        /* شريط تنبيه الأخطاء */
        .dark .bg-red-100 { background-color: rgba(220, 38, 38, .15) !important; }
        .dark .border-red-400 { border-color: rgba(220, 38, 38, .4) !important; }
        .dark .text-red-700 { color: #f87171 !important; }

        /* صناديق مساعدة */
        .dark .bg-yellow-50 { background-color: rgba(109, 14, 22, 0.1) !important; }
        .dark .text-yellow-800 { color: #fca5a5 !important; }
        .dark .border-yellow-400 { border-color: rgba(109, 14, 22, 0.3) !important; }

        /* عناصر الملخص */
        .dark .text-green-600 { color: #4ade80 !important; }

        /* زر التأكيد يحافظ على ألوان الهوية */
        .dark .bg-brand-dark,
        .dark .bg-brand-primary { background-color: #6d0e16 !important; color:#fff !important; }
        .dark .hover\:bg-brand-primary:hover,
        .dark .hover\:bg-brand-dark:hover { background-color: #500a10 !important; color:#fff !important; }

        /* حقول الإدخال */
        .dark input, .dark select, .dark textarea {
            background-color: #0d0d0d !important;
            color: #eeeeee !important;
            border-color: #262626 !important;
        }
        .dark input:focus, .dark select:focus, .dark textarea:focus {
            border-color: #6d0e16 !important;
            ring-color: #6d0e16 !important;
        }
        .dark input::placeholder, .dark textarea::placeholder { color: #666666; }
    </style>
@endpush

@section('content')
@php
    $giftOrderSelected = (bool) old('is_gift');
    $selectedSavedAddressId = old('saved_address_id', $addresses->first()->id ?? null);
@endphp
<div class="min-h-screen bg-[#f7f7f7] dark:bg-[#0a0a0a]">
    <div class="container mx-auto px-4 py-12">
        <div class="text-center mb-10">
            <h1 class="text-3xl md:text-4xl font-bold text-brand-text dark:text-gray-100">{{ __('checkout.page_heading') }}</h1>
            <p class="text-gray-500 mt-2 dark:text-gray-400">{{ __('checkout.page_subheading') }}</p>
        </div>

        {{-- عرض الأخطاء --}}
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-8 max-w-4xl mx-auto dark:bg-opacity-10 dark:text-red-300" role="alert">
                <strong class="font-bold">{{ __('checkout.fix_errors') }}</strong>
                <ul class="mt-2 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-8 max-w-4xl mx-auto dark:bg-opacity-10 dark:text-red-300">
                {{ session('error') }}
            </div>
        @endif

<form action="{{ route('checkout.store') }}" method="POST" id="checkout-form">
    @csrf
    <input type="hidden" name="address_option" value="saved">

    <div class="flex flex-col lg:flex-row gap-8 lg:gap-12">
        {{-- العمود الأيسر: الشحن + الدفع --}}
        <div class="lg:w-7/12 xl:w-2/3 space-y-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 dark:bg-gray-900 dark:border-gray-800">
                <div class="flex items-center gap-4 mb-5">
                    <span class="step-badge bg-brand-primary">1</span>
                    <h2 class="text-xl font-bold text-brand-text dark:text-gray-100">{{ __('checkout.shipping_info') }}</h2>
                </div>
                
                <div class="space-y-4" x-data="{ selectedAddressId: {{ $selectedSavedAddressId ?? 'null' }} }">
                    @if($addresses->isNotEmpty())
                    <div id="shipping_address_section" class="mb-4 {{ $giftOrderSelected ? 'hidden' : '' }}">
                        <h3 class="text-md font-semibold text-gray-800 mb-2 dark:text-gray-100">{{ __('checkout.choose_saved_address') }}</h3>
                        <div class="space-y-3" id="saved_addresses_list">
                            @foreach($addresses as $address)
                            <label class="address-card block border rounded-xl p-4 cursor-pointer transition hover:border-brand-primary dark:border-gray-800 dark:hover:border-brand-primary" :class="selectedAddressId == {{ $address->id }} ? 'selected' : ''">
                                <div class="flex items-center">
                                    <input type="radio" name="saved_address_id" value="{{ $address->id }}"
                                        class="saved-address-radio h-4 w-4 text-brand-primary focus:ring-brand-primary"
                                        @click="selectedAddressId = {{ $address->id }}"
                                        {{ (string) $selectedSavedAddressId === (string) $address->id ? 'checked' : '' }}>
                                    <div class="ml-3">
                                        <p class="font-semibold text-gray-800 dark:text-gray-100">{{ $address->governorate }}, {{ $address->city }}</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $address->address_details }}</p>
                                    </div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div id="shipping_address_empty_state" class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4 dark:bg-opacity-10 {{ $addresses->isEmpty() && ! $giftOrderSelected ? '' : 'hidden' }}">
                        <p class="text-yellow-800 dark:text-yellow-200">{{ __('checkout.no_address_yet') }}</p>
                    </div>

                    <a href="{{ route('profile.addresses.create') }}" id="shipping_address_actions" class="w-full text-left border rounded-xl p-4 flex items-center gap-3 text-brand-primary font-semibold hover:bg-gray-50 transition dark:border-gray-800 dark:hover:bg-gray-800/50 {{ $giftOrderSelected ? 'hidden' : '' }}">
                        <i class="bi bi-plus-circle-fill"></i>
                        <span>{{ __('checkout.add_new_address') }}</span>
                    </a>

                    <div class="mt-6 border rounded-xl p-4 bg-gray-50/70 dark:bg-gray-800/40 dark:border-gray-800">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="is_gift" id="is_gift" value="1" class="h-4 w-4 text-brand-primary focus:ring-brand-primary" {{ old('is_gift') ? 'checked' : '' }}>
                            <div>
                                <div class="font-semibold text-gray-800 dark:text-gray-100">{{ __('checkout.this_is_gift') }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('checkout.gift_notice') }}</div>
                            </div>
                        </label>

                        <div id="gift_fields" class="mt-4 space-y-3 {{ old('is_gift') ? '' : 'hidden' }}">
                            <div>
                                <label for="gift_recipient_name" class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('checkout.gift_recipient_name') }}</label>
                                <input type="text" id="gift_recipient_name" name="gift_recipient_name" value="{{ old('gift_recipient_name') }}" class="w-full rounded-md border-gray-300 focus:border-brand-primary focus:ring-brand-primary dark:border-gray-700" placeholder="{{ __('checkout.gift_recipient_name_ph') }}">
                            </div>
                            <div>
                                <label for="gift_recipient_phone" class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('checkout.gift_recipient_phone') }}</label>
                                <input type="text" id="gift_recipient_phone" name="gift_recipient_phone" value="{{ old('gift_recipient_phone') }}" class="w-full rounded-md border-gray-300 focus:border-brand-primary focus:ring-brand-primary dark:border-gray-700" placeholder="{{ __('checkout.gift_recipient_phone_ph') }}">
                            </div>
                            <div>
                                <label for="gift_recipient_address_details" class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('checkout.gift_recipient_address') }}</label>
                                <textarea id="gift_recipient_address_details" name="gift_recipient_address_details" rows="3" class="w-full rounded-md border-gray-300 focus:border-brand-primary focus:ring-brand-primary dark:border-gray-700" placeholder="{{ __('checkout.gift_recipient_address_ph') }}">{{ old('gift_recipient_address_details') }}</textarea>
                            </div>
                            <div id="gift_address_preview" class="rounded-lg border border-brand-primary/30 bg-brand-primary/5 px-4 py-3 {{ $giftOrderSelected && old('gift_recipient_address_details') ? '' : 'hidden' }}">
                                <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ __('checkout.confirmed_delivery_address') }}</div>
                                <p id="gift_address_preview_text" class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ old('gift_recipient_address_details') }}</p>
                            </div>
                            <div>
                                <label for="gift_message" class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('checkout.gift_message') }}</label>
                                <textarea id="gift_message" name="gift_message" rows="2" class="w-full rounded-md border-gray-300 focus:border-brand-primary focus:ring-brand-primary dark:border-gray-700" placeholder="{{ __('checkout.gift_message_ph') }}">{{ old('gift_message') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 dark:bg-gray-900 dark:border-gray-800">
                <div class="flex items-center gap-4 mb-5">
                    <span class="step-badge bg-brand-primary">2</span>
                    <h2 class="text-xl font-bold text-brand-text dark:text-gray-100">{{ __('checkout.payment_method') }}</h2>
                </div>
                <div class="bg-gray-50 p-4 rounded-xl border hover:border-brand-primary transition dark:bg-gray-800 dark:border-gray-800">
                    <label for="cash" class="flex items-center cursor-pointer">
                        <input id="cash" name="payment_method" type="radio"
                               class="h-4 w-4 text-brand-primary focus:ring-brand-primary"
                               value="cash_on_delivery" checked required>
                        <span class="ml-3 font-medium text-gray-800 dark:text-gray-100">{{ __('checkout.cash_on_delivery') }}</span>
                        <i class="bi bi-cash-coin text-xl text-green-600 ml-auto"></i>
                    </label>
                </div>
            </div>
        </div>

        {{-- العمود الأيمن: ملخص الطلب + استخدام المحفظة --}}
        <div class="lg:w-5/12 xl:w-1/3">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 sticky top-24 dark:bg-gray-900 dark:border-gray-800">
                <h2 class="text-xl font-bold text-brand-text mb-4 border-b pb-4 dark:text-gray-100 dark:border-gray-800">{{ __('common.order_summary') }}</h2>
                
                <div class="space-y-3 mb-4 max-h-64 overflow-y-auto pr-2">
                    @foreach($cartItems as $item)
                        <div class="flex justify-between items-center text-sm">
                            <div class="flex items-center gap-3">
                                <img src="{{ $item['product']->firstImage ? asset('storage/' . $item['product']->firstImage->image_path) : 'https://placehold.co/60x60' }}" alt="{{ $item['product']->name_translated }}" class="w-12 h-12 rounded-md object-cover">
                                <div>
                                    <p class="text-gray-800 font-semibold dark:text-gray-100">{{ $item['product']->name_translated }}</p>
                                    <p class="text-gray-500 dark:text-gray-400">{{ __('checkout.quantity_colon') }} {{ $item['quantity'] }}</p>
                                    @if(!empty($item['selected_options']))
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            @foreach($item['selected_options'] as $label => $value)
                                                <div>{{ $label }}: {{ $value }}</div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <span class="font-medium text-gray-800 dark:text-gray-100">{{ number_format($item['price'] * $item['quantity']) }} {{ __('checkout.currency') }}</span>
                        </div>
                    @endforeach
                </div>
                
                <div class="space-y-2 border-t pt-4 dark:border-gray-800">
                    <div class="flex justify-between font-semibold text-gray-800 dark:text-gray-100"><span>{{ __('checkout.subtotal') }}</span><span>{{ number_format($subtotal) }} {{ __('checkout.currency') }}</span></div>
                    <div class="flex justify-between text-green-600"><span>{{ __('checkout.discount') }}</span><span>- {{ number_format($discountValue) }} {{ __('checkout.currency') }}</span></div>
                    @if($isShippingEnabled)
                    <div class="flex justify-between text-gray-500 dark:text-gray-400"><span>{{ __('checkout.shipping') }}</span><span>{{ $shippingCost > 0 ? number_format($shippingCost) . ' ' . __('checkout.currency') : ($isFreeShippingEnabled ? __('checkout.free') : number_format($baseShippingCost) . ' ' . __('checkout.currency')) }}</span></div>
                    @endif

                    {{-- استخدام المحفظة --}}
                    <div class="mt-3 p-3 rounded-md border bg-gray-50 dark:bg-gray-800 dark:border-gray-800">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="use_wallet" id="use_wallet" value="1" class="h-4 w-4 text-brand-primary focus:ring-brand-primary">
                            <div>
                                <div class="font-semibold text-gray-800 dark:text-gray-100">{{ __('checkout.use_wallet') }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('checkout.current_balance') }} <span id="wallet_balance_text">{{ number_format($walletBalance ?? 0) }}</span> {{ __('checkout.currency') }}</div>
                            </div>
                        </label>

                        <div id="wallet_calc" class="mt-2 text-sm hidden">
                            <div class="flex justify-between text-gray-800 dark:text-gray-100"><span>{{ __('checkout.wallet_deduct') }}</span><span id="wallet_used_text">0</span></div>
                            <div class="flex justify-between text-gray-800 dark:text-gray-100"><span>{{ __('checkout.remaining_on_delivery') }}</span><span id="cod_due_text">0</span></div>
                        </div>
                    </div>

                    <div class="flex justify-between font-bold text-xl text-[#1a1a1a] border-t pt-2 mt-2 dark:text-gray-100 dark:border-gray-800">
                        <span>{{ __('checkout.total') }}</span>
                        <span id="final_total_text" class="text-[#6d0e16] dark:text-[#f0b0ad]">{{ number_format($finalTotal) }} {{ __('checkout.currency') }}</span>
                    </div>
                </div>

                <div class="mt-6">
                    <button id="checkout_submit_button" class="w-full bg-brand-dark text-white font-bold py-4 px-4 rounded-xl hover:bg-brand-primary transition duration-300 text-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 {{ $addresses->isEmpty() && ! $giftOrderSelected ? 'opacity-60 cursor-not-allowed' : '' }}" type="submit" @if($addresses->isEmpty() && ! $giftOrderSelected) disabled @endif>
                        <i class="bi bi-shield-check"></i>
                        {{ __('checkout.confirm_order') }}
                    </button>
                    <p id="checkout_submit_hint" class="text-xs text-center mt-2 {{ $giftOrderSelected ? 'text-gray-500 dark:text-gray-400' : ($addresses->isEmpty() ? 'text-red-500' : 'hidden') }}">
                        @if($giftOrderSelected)
                            {{ __('checkout.gift_address_will_be_used') }}
                        @elseif($addresses->isEmpty())
                            {{ __('checkout.must_add_address') }}
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</form>

    </div>
</div>
@endsection

@push('scripts')
    @php
        // تجهيز بيانات المنتجات الموجودة في السلة لاستخدامها مع Meta Pixel
        $checkoutContentIds = [];
        $checkoutContents = [];

        foreach ($cartItems as $item) {
            $checkoutContentIds[] = $item['product']->id;
            $checkoutContents[] = [
                'id'       => (string) $item['product']->id,
                'quantity' => (int) $item['quantity'],
            ];
        }

        $finalTotalNumber = (float) $finalTotal;
    @endphp

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const savedAddressesRadios = document.querySelectorAll('.saved-address-radio');
            const isGiftCheckbox = document.getElementById('is_gift');
            const giftFields = document.getElementById('gift_fields');
            const giftRecipientName = document.getElementById('gift_recipient_name');
            const giftRecipientPhone = document.getElementById('gift_recipient_phone');
            const giftRecipientAddress = document.getElementById('gift_recipient_address_details');
            const shippingAddressSection = document.getElementById('shipping_address_section');
            const shippingAddressActions = document.getElementById('shipping_address_actions');
            const shippingAddressEmptyState = document.getElementById('shipping_address_empty_state');
            const giftAddressPreview = document.getElementById('gift_address_preview');
            const giftAddressPreviewText = document.getElementById('gift_address_preview_text');
            const checkoutSubmitButton = document.getElementById('checkout_submit_button');
            const checkoutSubmitHint = document.getElementById('checkout_submit_hint');
            const checkoutForm = document.getElementById('checkout-form');
            
            // ✅ منع تكرار الطلب (Double-Submit Protection)
            let isFormSubmitting = false;
            if (checkoutForm && checkoutSubmitButton) {
                checkoutForm.addEventListener('submit', function (e) {
                    if (isFormSubmitting) {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }
                    isFormSubmitting = true;
                    checkoutSubmitButton.disabled = true;
                    checkoutSubmitButton.style.opacity = '0.6';
                    checkoutSubmitButton.innerHTML = '<i class="bi bi-hourglass-split"></i> جاري المعالجة...';
                });
            }

            function selectAddress(radio) {
                document.querySelectorAll('.address-card').forEach(card => card.classList.remove('selected'));
                radio.closest('.address-card').classList.add('selected');
            }

            savedAddressesRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.checked) selectAddress(this);
                });
            });

            const checkedRadio = document.querySelector('input[name="saved_address_id"]:checked');
            if (checkedRadio) selectAddress(checkedRadio);

            function hasSavedAddresses() {
                return savedAddressesRadios.length > 0;
            }

            function toggleSubmitState(isGift) {
                if (!checkoutSubmitButton) {
                    return;
                }

                const shouldDisable = !isGift && !hasSavedAddresses();

                checkoutSubmitButton.disabled = shouldDisable;
                checkoutSubmitButton.classList.toggle('opacity-60', shouldDisable);
                checkoutSubmitButton.classList.toggle('cursor-not-allowed', shouldDisable);

                if (!checkoutSubmitHint) {
                    return;
                }

                if (isGift) {
                    checkoutSubmitHint.textContent = '{{ __('checkout.gift_address_will_be_used') }}';
                    checkoutSubmitHint.className = 'text-xs text-center mt-2 text-gray-500 dark:text-gray-400';
                    return;
                }

                if (!hasSavedAddresses()) {
                    checkoutSubmitHint.textContent = '{{ __('checkout.must_add_address') }}';
                    checkoutSubmitHint.className = 'text-red-500 text-xs text-center mt-2';
                    return;
                }

                checkoutSubmitHint.textContent = '';
                checkoutSubmitHint.className = 'hidden';
            }

            function toggleGiftAddressPreview() {
                if (!giftAddressPreview || !giftRecipientAddress || !isGiftCheckbox) {
                    return;
                }

                const previewText = giftRecipientAddress.value.trim();
                const showPreview = isGiftCheckbox.checked && previewText !== '';

                giftAddressPreview.classList.toggle('hidden', !showPreview);

                if (giftAddressPreviewText) {
                    giftAddressPreviewText.textContent = previewText;
                }
            }

            function toggleGiftFields() {
                if (!isGiftCheckbox || !giftFields) return;

                const enabled = isGiftCheckbox.checked;
                giftFields.classList.toggle('hidden', !enabled);

                if (giftRecipientName) giftRecipientName.required = enabled;
                if (giftRecipientPhone) giftRecipientPhone.required = enabled;
                if (giftRecipientAddress) giftRecipientAddress.required = enabled;

                savedAddressesRadios.forEach(radio => {
                    radio.disabled = enabled;
                });

                if (shippingAddressSection) {
                    shippingAddressSection.classList.toggle('hidden', enabled);
                }

                if (shippingAddressActions) {
                    shippingAddressActions.classList.toggle('hidden', enabled);
                }

                if (shippingAddressEmptyState) {
                    shippingAddressEmptyState.classList.toggle('hidden', enabled || hasSavedAddresses());
                }

                toggleGiftAddressPreview();
                toggleSubmitState(enabled);
            }

            if (isGiftCheckbox) {
                isGiftCheckbox.addEventListener('change', toggleGiftFields);
                toggleGiftFields();
            }

            if (giftRecipientAddress) {
                giftRecipientAddress.addEventListener('input', toggleGiftAddressPreview);
                toggleGiftAddressPreview();
            }

            if (!isGiftCheckbox) {
                toggleSubmitState(false);
            }

            // ---- حساب المحفظة في الواجهة (عرض فقط)
            const useWallet       = document.getElementById('use_wallet');
            const walletBalance   = parseFloat('{{ (float)($walletBalance ?? 0) }}') || 0;
            const finalTotal      = parseFloat('{{ (float)$finalTotal }}') || 0;

            const walletCalcBox   = document.getElementById('wallet_calc');
            const walletUsedText  = document.getElementById('wallet_used_text');
            const codDueText      = document.getElementById('cod_due_text');

            function format(num){ return new Intl.NumberFormat('ar-IQ').format(Math.max(0, Math.round(num))); }

            function refreshWalletDisplay() {
                if (useWallet.checked) {
                    const willUse = Math.min(walletBalance, finalTotal);
                    const codDue  = Math.max(0, finalTotal - willUse);
                    walletUsedText.textContent = format(willUse) + ' {{ __('checkout.currency') }}';
                    codDueText.textContent     = format(codDue) + ' {{ __('checkout.currency') }}';
                    walletCalcBox.classList.remove('hidden');
                } else {
                    walletCalcBox.classList.add('hidden');
                }
            }

            if (useWallet) {
                useWallet.addEventListener('change', refreshWalletDisplay);
                refreshWalletDisplay();
            }

            // ===== Meta Pixel: InitiateCheckout =====
            if (typeof fbq === 'function') {
                fbq('track', 'InitiateCheckout', {
                    value: {{ $finalTotalNumber }},
                    currency: 'IQD',
                    contents: @json($checkoutContents),
                    content_ids: @json($checkoutContentIds),
                    content_type: 'product',
                    num_items: {{ count($checkoutContentIds) }}
                });
            }
        });
    </script>
@endpush
endpush