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
  // ذكرة الخصم الأصلي للحساب الذكي
  final double originalDiscount;
  final double originalSubtotal;

  CartState({
    this.items = const [],
    this.subtotal = 0.0,
    this.discount = 0.0,
    this.total = 0.0,
    this.count = 0,
    this.discountCode,
    this.isLoading = false,
    this.error,
    this.originalDiscount = 0.0,
    this.originalSubtotal = 0.0,
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
    double? originalDiscount,
    double? originalSubtotal,
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
      originalDiscount: originalDiscount ?? this.originalDiscount,
      originalSubtotal: originalSubtotal ?? this.originalSubtotal,
    );
  }

  /// حساب نسبة الخصم (نسبة مئوية)
  double get discountPercentage {
    try {
      if (originalSubtotal <= 0 || originalDiscount <= 0) return 0.0;
      final percentage = (originalDiscount / originalSubtotal) * 100;
      return percentage.isNaN || percentage.isInfinite ? 0.0 : percentage;
    } catch (_) {
      return 0.0;
    }
  }
}

class CartNotifier extends Notifier<CartState> {
  @override
  CartState build() {
    return CartState();
  }

  CartRepository get _repo => ref.read(cartRepositoryProvider);

  /// إعادة حساب الخصم بناءً على نسبة الخصم الأصلية
  double _recalculateDiscount(double newSubtotal) {
    try {
      if (state.originalSubtotal <= 0 || state.originalDiscount <= 0) {
        return 0.0;
      }

      // حساب نسبة الخصم من المجموع الأصلي
      final discountRatio = state.originalDiscount / state.originalSubtotal;
      
      // تطبيق النسبة على المجموع الجديد
      double newDiscount = (newSubtotal * discountRatio);
      
      // التأكد من عدم تجاوز الخصم للمجموع الجديد
      if (newDiscount > newSubtotal) {
        newDiscount = newSubtotal;
      }
      
      return newDiscount.isNaN || newDiscount.isInfinite ? 0.0 : newDiscount;
    } catch (_) {
      return 0.0;
    }
  }

  void _applyItemsSnapshot(List<CartItemModel> items) {
    try {
      final subtotal = items.fold<double>(0.0, (sum, item) => sum + item.total);
      
      // إعادة حساب الخصم ذكياً
      final recalculatedDiscount = _recalculateDiscount(subtotal);
      
      final newTotal = (subtotal - recalculatedDiscount);
      
      state = state.copyWith(
        items: items,
        subtotal: subtotal,
        discount: recalculatedDiscount,
        total: newTotal,
        count: items.length,
        isLoading: false,
      );
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: 'خطأ في تحديث السلة: $e',
      );
    }
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
        
        final fetchedSubtotal = (data['subtotal'] as num?)?.toDouble() ?? 0.0;
        final fetchedDiscount = (data['discount'] as num?)?.toDouble() ?? 0.0;

        state = state.copyWith(
          items: items,
          subtotal: fetchedSubtotal,
          discount: fetchedDiscount,
          total: (data['total'] as num?)?.toDouble() ?? 0.0,
          count: (data['count'] as num?)?.toInt() ?? 0,
          discountCode: data['discount_code'] as String?,
          isLoading: false,
          // حفظ معلومات الخصم الأصلية للحساب الذكي
          originalDiscount: fetchedDiscount,
          originalSubtotal: fetchedSubtotal,
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

  /// حذف الخصم وتصفيره
  Future<void> removeDiscount() async {
    try {
      state = state.copyWith(isLoading: true);
      await _repo.removeDiscount();
      
      // تحديث الحالة بحذف الخصم
      final newTotal = state.subtotal - 0.0; // جعلها واضحة أن الخصم = 0
      state = state.copyWith(
        discount: 0.0,
        total: newTotal,
        discountCode: null,
        originalDiscount: 0.0,
        originalSubtotal: 0.0,
        isLoading: false,
        clearDiscountCode: true,
      );
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
    }
  }

  /// تصفير الخصم عند الخروج من التطبيق (مسح الذاكرة)
  void clearDiscountOnAppExit() {
    state = state.copyWith(
      discount: 0.0,
      discountCode: null,
      originalDiscount: 0.0,
      originalSubtotal: 0.0,
      clearDiscountCode: true,
    );
  }
}

final cartProvider = NotifierProvider<CartNotifier, CartState>(() {
  return CartNotifier();
});
