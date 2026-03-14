<div class="product-card" x-data="{
    added: false,
    loadingAdd: false,
    isFavorite: {{ in_array($product->id, $favoriteProductIds ?? []) ? 'true' : 'false' }},
    loadingFav: false
}">
    <a href="{{ route('product.detail', $product) }}">
        <div class="product-image-container h-72">
            @if ($product->firstImage)
                <img src="{{ asset('storage/' . $product->firstImage->image_path) }}" class="w-full h-full object-cover">
            @else
                <img src="https://placehold.co/400x400?text=No+Image" class="w-full h-full object-cover">
            @endif
        </div>
    </a>
    <div class="p-4 text-right">
        <div class="flex justify-between items-start mb-2">
            <h3 class="font-semibold text-lg text-brand-dark">{{ $product->name_translated }}</h3>
            @auth
            <button 
                @click.prevent="
                    loadingFav = true;
                    fetch('{{ url('/wishlist/toggle-async') }}/{{ $product->id }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            isFavorite = !isFavorite;
                            window.dispatchEvent(new CustomEvent('wishlist-updated', { detail: { count: data.wishlistCount } }));
                        }
                    })
                    .finally(() => loadingFav = false);
                "
                class="text-gray-400 hover:text-red-500 transition-colors"
                :class="{ 'text-red-500': isFavorite }"
                :disabled="loadingFav"
            >
                <i class="bi text-2xl" :class="isFavorite ? 'bi-heart-fill' : 'bi-heart'"></i>
            </button>
            @endauth
        </div>
        <p class="text-brand-primary font-bold text-xl mb-4">{{ number_format($product->price) }} د.ع</p>
        <button 
            @click.prevent="
                loadingAdd = true;
                fetch('{{ route('cart.store') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ product_id: {{ $product->id }}, quantity: 1 })
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        added = true;
                        window.dispatchEvent(new CustomEvent('cart-updated', { detail: { cartCount: data.cartCount } }));
                        setTimeout(() => added = false, 2000);
                    } else {
                        alert(data.message || 'حدث خطأ ما.');
                    }
                })
                .finally(() => loadingAdd = false);
            "
            class="btn-primary w-full py-2 rounded-lg flex justify-center items-center gap-2 transition-all"
            :disabled="loadingAdd || added"
        >
            <span x-show="!added && !loadingAdd"><i class="bi bi-cart-plus"></i> أضف للسلة</span>
            <span x-show="loadingAdd"><i class="bi bi-arrow-repeat animate-spin"></i></span>
            <span x-show="added"><i class="bi bi-check-lg"></i> تمت الإضافة</span>
        </button>
    </div>
</div>
