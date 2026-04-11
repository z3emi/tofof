import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';

class WishlistNotifier extends Notifier<Set<int>> {
  static const _wishlistKey = 'wishlist_product_ids';

  @override
  Set<int> build() {
    _load();
    return <int>{};
  }

  Future<void> _load() async {
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getStringList(_wishlistKey) ?? const [];
    state = raw.map(int.tryParse).whereType<int>().toSet();
  }

  bool isInWishlist(int productId) => state.contains(productId);

  Future<bool> toggle(int productId) async {
    final updated = {...state};
    final added = !updated.contains(productId);

    if (added) {
      updated.add(productId);
    } else {
      updated.remove(productId);
    }

    state = updated;

    final prefs = await SharedPreferences.getInstance();
    await prefs.setStringList(_wishlistKey, updated.map((id) => id.toString()).toList());

    return added;
  }
}

final wishlistProvider = NotifierProvider<WishlistNotifier, Set<int>>(() {
  return WishlistNotifier();
});
