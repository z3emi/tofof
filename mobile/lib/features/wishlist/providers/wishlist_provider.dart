import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/network/dio_client.dart';
import '../../../core/network/api_constants.dart';
import '../../../shared/models/product_model.dart';

class WishlistNotifier extends Notifier<Set<int>> {
  @override
  Set<int> build() {
    // Load wishlist on start if authenticated
    Future.microtask(() => fetchWishlist());
    return {};
  }

  Future<void> fetchWishlist() async {
    try {
      final dio = ref.read(dioProvider);
      final res = await dio.get(ApiConstants.wishlist);
      final data = res.data['data'];
      if (data is List) {
        final ids = data
            .map((e) => (e as Map<String, dynamic>)['product_id'] ?? e['id'])
            .whereType<int>()
            .toSet();
        state = ids;
      }
    } catch (_) {}
  }

  bool isFavorite(int productId) => state.contains(productId);

  Future<void> toggle(int productId) async {
    // Optimistic update
    final hadIt = state.contains(productId);
    if (hadIt) {
      state = {...state}..remove(productId);
    } else {
      state = {...state, productId};
    }
    try {
      final dio = ref.read(dioProvider);
      await dio.post('${ApiConstants.wishlist}/$productId/toggle');
    } catch (_) {
      // Rollback on error
      if (hadIt) {
        state = {...state, productId};
      } else {
        state = {...state}..remove(productId);
      }
    }
  }
}

final wishlistProvider = NotifierProvider<WishlistNotifier, Set<int>>(() => WishlistNotifier());
