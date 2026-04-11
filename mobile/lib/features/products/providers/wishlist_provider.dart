import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../auth/data/auth_repository.dart';

class WishlistNotifier extends Notifier<Set<int>> {
  @override
  Set<int> build() {
    _loadFromApi();
    return <int>{};
  }

  Future<void> _loadFromApi() async {
    try {
      final response = await ref.read(authRepositoryProvider).fetchFavorites();
      final data =
          response['data'] as Map<String, dynamic>? ?? <String, dynamic>{};
      final items = (data['items'] as List?) ?? const [];

      state = items
          .whereType<Map<String, dynamic>>()
          .map((item) => item['id'])
          .whereType<num>()
          .map((id) => id.toInt())
          .toSet();
    } catch (_) {
      // Keep empty set for unauthenticated/failed state.
    }
  }

  bool isInWishlist(int productId) => state.contains(productId);

  Future<bool> toggle(int productId) async {
    final previous = {...state};
    final added = !previous.contains(productId);

    final optimistic = {...previous};
    if (added) {
      optimistic.add(productId);
    } else {
      optimistic.remove(productId);
    }
    state = optimistic;

    try {
      await ref.read(authRepositoryProvider).toggleFavorite(productId);
      return added;
    } catch (_) {
      state = previous;
      rethrow;
    }
  }

  Future<void> reload() async {
    await _loadFromApi();
  }
}

final wishlistProvider = NotifierProvider<WishlistNotifier, Set<int>>(() {
  return WishlistNotifier();
});
