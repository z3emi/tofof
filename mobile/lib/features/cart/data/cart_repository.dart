import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_constants.dart';
import '../../../core/network/dio_client.dart';

final cartRepositoryProvider = Provider<CartRepository>((ref) {
  return CartRepository(ref.watch(dioProvider));
});

class CartRepository {
  final Dio _dio;

  CartRepository(this._dio);

  Future<Map<String, dynamic>> fetchCart() async {
    try {
      final response = await _dio.get(ApiConstants.cart);
      return response.data;
    } on DioException catch (e) {
      if (e.response?.statusCode == 404 || e.response?.statusCode == 400) {
        return {'success': false, 'data': null};
      }
      throw e.response?.data['message'] ?? 'فشل جلب سلة المشتريات';
    }
  }

  Future<Map<String, dynamic>> addToCart(int productId, int quantity, [Map<String, dynamic>? options]) async {
    try {
      final response = await _dio.post(
        ApiConstants.cart,
        data: {
          'product_id': productId,
          'quantity': quantity,
          if (options != null) 'selected_options': options,
        },
      );
      return response.data as Map<String, dynamic>;
    } on DioException catch (e) {
      if (e.response?.statusCode == 429) {
        throw 'تم تجاوز الحد المسموح للطلبات، حاول بعد قليل';
      }
      throw e.response?.data['message'] ?? 'فشل إضافة المنتج للسلة';
    }
  }

  Future<Map<String, dynamic>> updateQuantity(String selectionKey, int quantity) async {
    try {
      final response = await _dio.patch(
        '${ApiConstants.cart}/$selectionKey',
        data: {'quantity': quantity},
      );
      return response.data as Map<String, dynamic>;
    } on DioException catch (e) {
      if (e.response?.statusCode == 429) {
        throw 'تم تجاوز الحد المسموح للطلبات، حاول بعد قليل';
      }
      throw e.response?.data['message'] ?? 'فشل تعديل الكمية';
    }
  }

  Future<Map<String, dynamic>> removeFromCart(String selectionKey) async {
    try {
      final response = await _dio.delete('${ApiConstants.cart}/$selectionKey');
      return response.data as Map<String, dynamic>;
    } on DioException catch (e) {
      if (e.response?.statusCode == 429) {
        throw 'تم تجاوز الحد المسموح للطلبات، حاول بعد قليل';
      }
      throw e.response?.data['message'] ?? 'فشل إزالة المنتج';
    }
  }

  Future<void> applyDiscount(String code) async {
    try {
      await _dio.post('${ApiConstants.cart}/discount', data: {'code': code});
    } on DioException catch (e) {
      throw e.response?.data['message'] ?? 'كود الخصم غير صالح أو منتهي';
    }
  }

  Future<void> removeDiscount() async {
    try {
      await _dio.delete('${ApiConstants.cart}/discount');
    } catch (_) {}
  }

  Future<Map<String, dynamic>> placeOrder({
    required int addressId,
    required String paymentMethod,
    double useWalletAmount = 0,
  }) async {
    try {
      final response = await _dio.post(
        ApiConstants.checkout,
        data: {
          'address_id': addressId,
          'payment_method': paymentMethod,
          'use_wallet_amount': useWalletAmount,
        },
      );
      return response.data as Map<String, dynamic>;
    } on DioException catch (e) {
      if (e.response?.statusCode == 429) {
        throw 'تم تجاوز الحد المسموح للطلبات، حاول بعد قليل';
      }
      throw e.response?.data['message'] ?? 'فشل إرسال الطلب';
    }
  }
}
