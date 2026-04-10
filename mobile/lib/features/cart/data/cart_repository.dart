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

  Future<void> addToCart(int productId, int quantity, [Map<String, dynamic>? options]) async {
    try {
      await _dio.post(
        '${ApiConstants.cart}/add',
        data: {
          'product_id': productId,
          'quantity': quantity,
          if (options != null) 'selected_options': options,
        },
      );
    } on DioException catch (e) {
      throw e.response?.data['message'] ?? 'فشل إضافة المنتج للسلة';
    }
  }

  Future<void> updateQuantity(String selectionKey, int quantity) async {
    try {
      await _dio.put(
        '${ApiConstants.cart}/update/$selectionKey',
        data: {'quantity': quantity},
      );
    } on DioException catch (e) {
      throw e.response?.data['message'] ?? 'فشل تعديل الكمية';
    }
  }

  Future<void> removeFromCart(String selectionKey) async {
    try {
      await _dio.delete('${ApiConstants.cart}/remove/$selectionKey');
    } on DioException catch (e) {
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
}
