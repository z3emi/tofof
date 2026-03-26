@extends('layouts.app')

@section('title', __('cart.title'))

@section('content')
<style>
  /* ===== هوية بصرية موحدة لصفحة السلة ===== */
  .bg-gray-50 { background: #f8f4f4 !important; }

  /* ألوان الأزرار في الوضع الفاتح */
  .bg-brand-primary,
  .bg-brand-dark {
    background: #6d0e16 !important;
    color: #fff !important;
  }
  .hover\:bg-brand-primary:hover,
  .hover\:bg-brand-dark:hover {
    background: #a61c20 !important;
    color: #fff !important;
  }
  .bg-gray-200 {
    background: #ead0d2 !important;
    color: #6d0e16 !important;
  }
  .hover\:bg-gray-300:hover {
    background: #cd8985 !important;
    color: #fff !important;
  }

  /* خلفية عامة في الوضع الداكن */
  .dark .bg-gray-50 { background: #0d0d0f !important; }

  /* بطاقات */
  .dark .bg-white { background: #161b27 !important; color: #e5e7eb !important; }
  .dark .shadow-sm { box-shadow: 0 2px 4px rgba(0,0,0,.7) !important; }

  /* نصوص */
  .dark .text-gray-700,
  .dark .text-gray-600,
  .dark .text-gray-500 { color: #d1d5db !important; }
  .dark .text-gray-300 { color: #9ca3af !important; }
  .dark .text-brand-text,
  .dark .text-brand-dark { color: #f9fafb !important; } /* النصوص الأساسية */

  /* روابط */
  .dark a.text-gray-500 { color: #9ca3af !important; }
  .dark a.text-gray-500:hover { color: #f8c7ca !important; }

  /* الحقول */
  .dark input[type="number"],
  .dark input[type="text"] {
    background: #1a1d24 !important;
    color: #f3f4f6 !important;
    border-color: #2a303d !important;
  }

  /* أزرار زيادة/نقصان الكمية */
  .dark .hover\:bg-gray-100:hover { background: #1e2430 !important; }

  /* أزرار رئيسية (إتمام عملية الشراء مثلاً) */
  .dark .bg-brand-dark {
    background: #6d0e16 !important;
    color: #fff !important;
  }
  .dark .bg-brand-primary {
    background: #6d0e16 !important;
    color: #fff !important;
  }
  .dark .bg-brand-dark:hover {
    background: #a61c20 !important;
  }
  .dark .hover\:bg-brand-primary:hover { background: #a61c20 !important; }

  /* أزرار رمادية (مثل تطبيق الكوبون) */
  .dark .bg-gray-200 { background: #2a303d !important; color: #f9fafb !important; }
  .dark .hover\:bg-gray-300:hover { background: #363d4c !important; }

  /* شريط التقدّم */
  .dark .w-full.bg-gray-200 { background: #2a303d !important; }
  .dark .bg-green-500 { background: #10b981 !important; }

  /* تنبيهات */
  .dark .bg-green-50 { background: rgba(16,185,129,.15) !important; color: #6ee7b7 !important; }
  .dark .text-green-600 { color: #34d399 !important; }

  /* أيقونات */
  .dark i.bi { color: currentColor; }
</style>

<div class="bg-gray-50 min-h-screen"
    x-data="cartState()"
    x-init="recalculateTotal()"
>
    <div class="container mx-auto px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-brand-text">{{ __('cart.title') }} (<span x-text="Object.keys(cartItems).length"></span>)</h1>
            <a href="{{ route('shop') }}" class="text-sm text-gray-500 hover:text-brand-primary">
              <i class="bi bi-arrow-left-short"></i> {{ __('common.continue_shopping') }}
            </a>
        </div>

        <template x-if="Object.keys(cartItems).length === 0">
            <div class="text-center bg-white p-10 rounded-lg shadow-md">
                <i class="bi bi-cart-x text-6xl text-gray-300 mb-4"></i>
                <h2 class="text-xl sm:text-2xl font-semibold text-gray-700 mb-2">{{ __('cart.cart_empty_title') }}</h2>
                <p class="text-gray-500 mb-6">{{ __('cart.cart_empty_desc') }}</p>
                <a href="{{ route('shop') }}" class="inline-block bg-brand-primary text-white font-bold py-3 px-6 rounded-md hover:bg-brand-dark transition duration-300">
                  {{ __('common.back_to_store') }}
                </a>
            </div>
        </template>

        <template x-if="Object.keys(cartItems).length > 0">
            <div class="flex flex-col lg:flex-row gap-8 lg:gap-12">
                {{-- المنتجات --}}
                <div class="lg:w-7/12 xl:w-2/3">
                    <div class="space-y-4">
                        <template x-for="item in Object.values(cartItems)" :key="item.row_id">
                            <div class="bg-white rounded-lg shadow-sm p-4 flex gap-4">
                                <a :href="`/product/${item.product.slug}`" class="w-24 h-24 flex-shrink-0">
                                    <img :src="item.product.first_image ? `/storage/${item.product.first_image.image_path}` : 'https://placehold.co/150x150?text=No+Image'" :alt="'{{ app()->getLocale() }}' === 'en' && item.product.name_en ? item.product.name_en : item.product.name_ar" class="w-full h-full object-cover rounded-md">
                                </a>
                                <div class="flex flex-col flex-grow w-full">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <a :href="`/product/${item.product.slug}`" class="font-bold text-lg text-brand-text hover:text-brand-primary" x-text="'{{ app()->getLocale() }}' === 'en' && item.product.name_en ? item.product.name_en : item.product.name_ar"></a>
                                            <p class="text-sm text-gray-500">SKU: <span x-text="item.product.sku || 'N/A'"></span></p>

                                            <template x-if="item.selected_options && Object.keys(item.selected_options).length">
                                              <div class="mt-2 text-xs text-gray-600">
                                                <template x-for="([label, value], idx) in Object.entries(item.selected_options)" :key="`${item.row_id}-${idx}`">
                                                  <div>
                                                    <span class="font-semibold" x-text="label + ':'"></span>
                                                    <span x-text="value"></span>
                                                  </div>
                                                </template>
                                              </div>
                                            </template>

                                            <template x-if="isOut(item)">
                                              <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-bold mt-2" style="background:#9CA3AF; color:#fff;">
                                                <i class="bi bi-x-circle"></i> {{ __('common.out_of_stock') }}
                                              </span>
                                            </template>

                                            <template x-if="!isOut(item) && isOnSale(item.product)">
                                              <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-bold mt-2 ml-2" style="background:#6d0e16; color:#ffe4e6; border: 1px solid #cd8985;">
                                                <i class="bi bi-tag"></i> {{ __('common.discount') }}
                                              </span>
                                            </template>
                                        </div>
                                        <button @click="removeItem(item.row_id)" class="text-gray-400 hover:text-red-500 transition" title="{{ __('cart.remove_product') }}">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                    <div class="flex items-end justify-between mt-auto">
                                        <div>
                                            <div class="flex items-center border rounded-md overflow-hidden"
                                                 :class="{'opacity-60 pointer-events-none': isOut(item)}">
                                                <button @click="updateQuantity(item.row_id, item.quantity + 1)" class="px-3 py-1 text-lg hover:bg-gray-100">+</button>
                                                <input type="number" x-model.number="item.quantity" @change="updateQuantity(item.row_id, item.quantity)" class="w-12 text-center border-x focus:outline-none">
                                                <button @click="updateQuantity(item.row_id, item.quantity - 1)" class="px-3 py-1 text-lg hover:bg-gray-100">-</button>
                                            </div>
                                            
                                            <template x-if="showStockInfo(item)">
                                              <p class="text-xs mt-1 text-right" style="color: #cd8985;"></p>
                                            </template>
                                        </div>

                                        <div class="text-right">
                                          <p class="font-bold text-lg text-brand-dark" x-text="`${formatPrice(itemLineTotal(item))} {{ __('common.currency') }}`"></p>
                                          <template x-if="isOnSale(item.product)">
                                            <p class="text-sm text-gray-500">
                                              <span class="font-semibold" x-text="`${formatPrice(effectivePrice(item.product))} {{ __('common.currency') }}`"></span>
                                              <span class="line-through ml-2" x-text="`${formatPrice(item.product.price)} {{ __('common.currency') }}`"></span>
                                              <span class="ml-1">{{ __('cart.per_piece') }}</span>
                                            </p>
                                          </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- ملخص --}}
                <div class="lg:w-5/12 xl:w-1/3">
                    <div class="bg-white p-6 rounded-lg shadow-sm sticky top-24">
                        <h2 class="text-xl font-bold mb-4">{{ __('common.order_summary') }}</h2>
                        
                        <div class="space-y-3 text-gray-700">
                          <div class="flex justify-between"><span>{{ __('cart.subtotal') }}</span><span class="font-semibold" x-text="`${formatPrice(subtotal)} {{ __('common.currency') }}`"></span></div>
                          <div class="flex justify-between"><span>{{ __('cart.discount_label') }}</span><span class="font-semibold text-green-600" x-text="`- ${formatPrice(discount)} {{ __('common.currency') }}`"></span></div>
                            @if($isShippingEnabled)
                          <div class="flex justify-between"><span>{{ __('cart.shipping_label') }}</span><span class="font-semibold" x-text="shippingCost > 0 ? `${formatPrice(shippingCost)} {{ __('common.currency') }}` : '{{ __('common.free_shipping') }}'"></span></div>
                            @endif
                        </div>

                        <div class="flex justify-between font-bold text-xl border-t mt-4 pt-4"><span>{{ __('cart.total_label') }}</span><span x-text="`${formatPrice(subtotal - discount + shippingCost)} {{ __('common.currency') }}`"></span></div>

                        @if($isShippingEnabled && $isFreeShippingEnabled)
                        <div class="mt-4 text-center">
                            {{-- ✅ [تعديل] تحديث حد الشحن المجاني هنا --}}
                            <div x-show="subtotal < freeShippingThreshold" style="display: none;">
                                <p class="text-sm text-gray-600 mb-2">
                                    {{ __('cart.remaining_free_ship') }} <strong class="text-brand-primary" x-text="formatPrice(Math.max(0, freeShippingThreshold - subtotal))"></strong> {{ __('cart.remaining_free_ship_2') }}
                                </p>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-green-500 h-2.5 rounded-full" :style="`width: ${freeShippingThreshold > 0 ? Math.min(100, (subtotal / freeShippingThreshold) * 100) : 100}%`"></div>
                                </div>
                            </div>
                            {{-- ✅ [تعديل] تحديث حد الشحن المجاني هنا --}}
                            <div x-show="subtotal >= freeShippingThreshold" class="text-green-600 font-semibold p-2 bg-green-50 rounded-md" style="display: none;">
                                <p>{{ __('cart.got_free_shipping') }}</p>
                            </div>
                        </div>
                        @endif

                        <div class="mt-6">
                            <a href="{{ route('checkout.index') }}" class="block text-center w-full bg-brand-dark text-white font-bold py-3 px-4 rounded-md hover:bg-brand-primary transition duration-300"
                               :class="{'opacity-60 pointer-events-none': anyOutOfStock()}"
                               :aria-disabled="anyOutOfStock()">
                                {{ __('cart.proceed_to_checkout') }}
                            </a>

                            <p class="mt-2 text-sm" x-show="anyOutOfStock()" style="color:#ef4444; display:none;">
                                {{ __('cart.out_of_stock_warning') }} <strong>{{ __('cart.out_of_stock_warning_2') }}</strong> {{ __('cart.out_of_stock_warning_3') }}
                            </p>
                        </div>
                        
                        <div class="mt-4">
                            <template x-if="discount <= 0">
                                <form @submit.prevent="applyDiscount" class="flex gap-2">
                                  <input type="text" x-model="discountCode" placeholder="{{ __('cart.add_coupon') }}" class="flex-1 border rounded-md px-3 py-2 text-right focus:ring-2 focus:ring-brand-primary">
                                  <button type="submit" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition">{{ __('common.apply') }}</button>
                                </form>
                            </template>
                            <template x-if="discount > 0">
                                <div class="bg-green-100 text-green-800 p-2 rounded text-sm flex justify-between items-center">
                                  <span>{{ __('cart.coupon_applied') }} <strong x-text="discountCode"></strong></span>
                                  <button @click="removeDiscount" class="text-red-600 hover:text-red-800 font-bold" title="{{ __('cart.remove_coupon') }}">&times;</button>
                                </div>
                            </template>
                            <p x-show="feedbackMessage" :class="{ 'text-green-600': feedbackType === 'success', 'text-red-600': feedbackType === 'error' }" class="text-sm mt-2" x-text="feedbackMessage"></p>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
@endsection

@push('scripts')
<script>
  function cartState() {
    return {
      cartItems: @json($cartItems),
      subtotal: {{ (float)$total }},
      discount: {{ (float)$discountValue }},
      shippingCost: {{ (float)$shippingCost }},
      baseShippingCost: {{ (float)$baseShippingCost }},
      freeShippingThreshold: {{ (int)$freeShippingThreshold }},
      isFreeShippingEnabled: {{ $isFreeShippingEnabled ? 'true' : 'false' }},
      discountCode: "{{ session('discount_code', '') }}",
      feedbackMessage: "{{ session('discount_error') ?: (session('discount_code') ? __('cart.coupon_applied') . ' ' . session('discount_code') : '') }}",
      feedbackType: "{{ session('discount_error') ? 'error' : (session('discount_code') ? 'success' : '') }}",

      getMax(item) {
        const q = (item?.product?.quantity ?? item?.product?.stock ?? item?.product?.stock_quantity ?? item?.product?.stock_qty ?? null);
        return (q === null || q === undefined) ? null : Math.max(0, parseInt(q, 10));
      },
      isOut(item) {
        const max = this.getMax(item);
        if (max === null) return true;
        return max <= 0;
      },
      anyOutOfStock() {
        return Object.values(this.cartItems).some(it => this.isOut(it));
      },
      showStockInfo(item) {
          if (this.isOut(item)) {
              return false;
          }
          const max = this.getMax(item);
          if (max === null) return false;
          return max <= 10 || item.quantity >= max;
      },
      effectivePrice(prod) {
        const base = parseInt(prod?.price ?? 0, 10) || 0;
        const sale = (prod?.sale_price != null && prod.sale_price !== '') ? parseInt(prod.sale_price, 10) : null;
        const now = new Date();
        const starts = prod?.sale_starts_at ? new Date(prod.sale_starts_at) : null;
        const ends = prod?.sale_ends_at ? new Date(prod.sale_ends_at) : null;
        const within = (!starts || now >= starts) && (!ends || now <= ends);
        if (sale && sale > 0 && sale < base && within) return sale;
        return base;
      },
      isOnSale(prod) {
        const base = parseInt(prod?.price ?? 0, 10) || 0;
        const eff = this.effectivePrice(prod);
        return eff < base;
      },
      itemLineTotal(item) {
        return this.effectivePrice(item.product) * item.quantity;
      },
      updateQuantity(rowId, newQuantity) {
        const item = this.cartItems[rowId];
        if (!item) return;
        if (this.isOut(item)) { return; }
        const max = this.getMax(item);
        if (newQuantity < 1) newQuantity = 1;
        if (max !== null && newQuantity > max) newQuantity = max;
        this.cartItems[rowId].quantity = newQuantity;
        this.updateCartOnServer(rowId, newQuantity);
      },
      removeItem(rowId) {
        fetch("{{ route('cart.destroy') }}", {
          method: "POST",
          headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}", "Content-Type": "application/json", "Accept": "application/json" },
          body: JSON.stringify({ row_id: rowId })
        })
        .then(res => res.json())
        .then(data => {
          if(data.success) {
            delete this.cartItems[rowId];
            window.dispatchEvent(new CustomEvent("cart-updated", { detail: { cartCount: data.cartCount } }));
            this.recalculateTotal();
          }
        });
      },
      updateCartOnServer(rowId, quantity) {
        fetch("{{ route('cart.update') }}", {
          method: "POST",
          headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}", "Content-Type": "application/json", "Accept": "application/json" },
          body: JSON.stringify({ row_id: rowId, quantity: quantity })
        })
        .then(res => res.json())
        .then(data => {
          if(!data.success && (data.reason === "out_of_stock" || data.available !== undefined)) {
            const it = this.cartItems[rowId];
            const available = Math.max(0, parseInt(data.available ?? 0, 10));
            if (it) {
              it.quantity = available > 0 ? available : 1;
            }
          }
          if(data.success) {
            window.dispatchEvent(new CustomEvent("cart-updated", { detail: { cartCount: data.cartCount } }));
          }
          this.recalculateTotal();
        });
      },
      recalculateTotal() {
        let newTotal = 0;
        for (const id in this.cartItems) {
          const it = this.cartItems[id];
          newTotal += this.itemLineTotal(it);
        }
        this.subtotal = newTotal;

        const threshold = Math.max(0, parseFloat(this.freeShippingThreshold ?? 0));
        const baseShipping = Math.max(0, parseFloat(this.baseShippingCost ?? 0));
        const hasFreeShipping = this.isFreeShippingEnabled && ((threshold > 0 && newTotal >= threshold) || threshold === 0);
        this.shippingCost = hasFreeShipping ? 0 : baseShipping;
      },
      applyDiscount() {
        fetch("{{ route('cart.applyDiscount') }}", {
          method: "POST",
          headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}", "Content-Type": "application/json", "Accept": "application/json" },
          body: JSON.stringify({ discount_code: this.discountCode })
        })
        .then(res => res.json().then(data => ({status: res.status, body: data})))
        .then(({status, body}) => {
          this.feedbackMessage = body.message;
          if (status === 200) {
            this.feedbackType = "success";
            this.discount = body.discount_value;
            this.discountCode = body.discount_code;
          } else {
            this.feedbackType = "error";
            this.discount = 0;
          }
        })
        .catch(() => {
          this.feedbackMessage = "{{ __('common.connection_error') }}";
          this.feedbackType = "error";
        });
      },
      removeDiscount() {
        fetch("{{ route('cart.removeDiscount') }}", {
          method: "POST",
          headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}", "Accept": "application/json" }
        })
        .then(res => res.json())
        .then(data => {
          if(data.success) {
            this.discount = 0;
            this.discountCode = "";
            this.feedbackMessage = data.message;
            this.feedbackType = "success";
          }
        });
      },
      formatPrice(price) {
        return new Intl.NumberFormat("en-US").format(price);
      }
    };
  }
</script>
@endpush
