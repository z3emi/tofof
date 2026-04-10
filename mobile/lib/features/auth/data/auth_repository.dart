import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_constants.dart';
import '../../../core/network/dio_client.dart';
import '../../../shared/models/user_model.dart';

final authRepositoryProvider = Provider<AuthRepository>((ref) {
  final dio = ref.watch(dioProvider);
  return AuthRepository(dio);
});

class AuthRepository {
  final Dio _dio;

  AuthRepository(this._dio);

  Future<Map<String, dynamic>> login(String email, String password) async {
    try {
      final response = await _dio.post(
        ApiConstants.login,
        data: {'email': email, 'password': password},
      );
      return response.data;
    } on DioException catch (e) {
      throw e.response?.data['message'] ?? 'حدث خطأ غير متوقع لتسجيل الدخول';
    }
  }

  Future<Map<String, dynamic>> register(Map<String, dynamic> data) async {
    try {
      final response = await _dio.post(
        ApiConstants.register,
        data: data,
      );
      return response.data;
    } on DioException catch (e) {
      throw e.response?.data['message'] ?? 'فشل إنشاء الحساب. تأكد من البيانات';
    }
  }

  Future<UserModel> fetchMe() async {
    try {
      final response = await _dio.get(ApiConstants.me);
      return UserModel.fromJson(response.data['data']['user']);
    } on DioException catch (e) {
      throw e.response?.data['message'] ?? 'فشل جلب بيانات المستخدم';
    }
  }

  Future<void> logout() async {
    try {
      await _dio.post(ApiConstants.logout);
    } catch (_) {
      // Ignore errors on logout
    }
  }
}
