import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../shared/models/cart_item_model.dart';
import '../data/cart_repository.dart';

class CartState {
  final List<CartItemModel> items;
  final double subtotal;
  final double discount;
  final double total;
  final int count;
  final String? discountCode;
  final bool isLoading;
  final String? error;

  CartState({
    this.items = const [],
    this.subtotal = 0.0,
    this.discount = 0.0,
    this.total = 0.0,
    this.count = 0,
    this.discountCode,
    this.isLoading = false,
    this.error,
  });

  CartState copyWith({
    List<CartItemModel>? items,
    double? subtotal,
    double? discount,
    double? total,
    int? count,
    String? discountCode,
    bool clearDiscountCode = false,
    bool? isLoading,
    String? error,
    bool clearError = false,
  }) {
    return CartState(
      items: items ?? this.items,
      subtotal: subtotal ?? this.subtotal,
      discount: discount ?? this.discount,
      total: total ?? this.total,
      count: count ?? this.count,
      discountCode: clearDiscountCode
          ? null
          : (discountCode ?? this.discountCode),
      isLoading: isLoading ?? this.isLoading,
      error: clearError ? null : (error ?? this.error),
    );
  }
}

class CartNotifier extends Notifier<CartState> {
  @override
  CartState build() {
    return CartState();
  }

  CartRepository get _repo => ref.read(cartRepositoryProvider);

  void _applyItemsSnapshot(List<CartItemModel> items) {
    final subtotal = items.fold<double>(0.0, (sum, item) => sum + item.total);
    final discount = state.discount;
    state = state.copyWith(
      items: items,
      subtotal: subtotal,
      total: subtotal - discount,
      count: items.length,
      isLoading: false,
    );
  }

  List<CartItemModel> _extractItems(Map<String, dynamic> response) {
    final data =
        response['data'] as Map<String, dynamic>? ?? <String, dynamic>{};
    final rawItems = (data['items'] as List?) ?? const [];
    return rawItems
        .whereType<Map<String, dynamic>>()
        .map(CartItemModel.fromJson)
        .toList();
  }

  Future<void> fetchCart() async {
    state = state.copyWith(isLoading: true, clearError: true);
    try {
      final res = await _repo.fetchCart();
      if (res['success'] == true && res['data'] != null) {
        final data = res['data'] as Map<String, dynamic>;
        final items = ((data['items'] as List?) ?? [])
            .map((e) => CartItemModel.fromJson(e as Map<String, dynamic>))
            .toList();

        state = state.copyWith(
          items: items,
          subtotal: (data['subtotal'] as num?)?.toDouble() ?? 0.0,
          discount: (data['discount'] as num?)?.toDouble() ?? 0.0,
          total: (data['total'] as num?)?.toDouble() ?? 0.0,
          count: (data['count'] as num?)?.toInt() ?? 0,
          discountCode: data['discount_code'] as String?,
          isLoading: false,
        );
      } else {
        // Empty cart
        state = CartState();
      }
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
    }
  }

  Future<bool> addToCart(
    int productId,
    int quantity, [
    Map<String, dynamic>? options,
  ]) async {
    try {
      state = state.copyWith(isLoading: true, clearError: true);
      final res = await _repo.addToCart(productId, quantity, options);
      _applyItemsSnapshot(_extractItems(res));
      return true;
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
      return false;
    }
  }

  Future<void> updateQuantity(String key, int quantity) async {
    if (quantity < 1) return;
    try {
      final res = await _repo.updateQuantity(key, quantity);
      _applyItemsSnapshot(_extractItems(res));
    } catch (e) {
      state = state.copyWith(error: e.toString());
    }
  }

  Future<void> removeItem(String key) async {
    try {
      final res = await _repo.removeFromCart(key);
      _applyItemsSnapshot(_extractItems(res));
    } catch (e) {
      state = state.copyWith(error: e.toString());
    }
  }

  Future<bool> applyDiscount(String code) async {
    try {
      state = state.copyWith(isLoading: true);
      await _repo.applyDiscount(code);
      await fetchCart();
      return true;
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
      return false;
    }
  }
}

final cartProvider = NotifierProvider<CartNotifier, CartState>(() {
  return CartNotifier();
});
