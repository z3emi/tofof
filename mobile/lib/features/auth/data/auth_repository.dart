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

  Future<Map<String, dynamic>> login({
    required String phoneNumber,
    required String password,
  }) async {
    try {
      final response = await _dio.post(
        ApiConstants.login,
        data: {
          'phone_number': phoneNumber,
          'password': password,
        },
      );
      return response.data;
    } on DioException catch (e) {
      throw e.response?.data['message'] ?? 'حدث خطأ غير متوقع أثناء تسجيل الدخول';
    }
  }

  Future<Map<String, dynamic>> register({
    required String name,
    required String phoneNumber,
    required String password,
    required String passwordConfirmation,
    String? referralCode,
  }) async {
    try {
      final response = await _dio.post(
        ApiConstants.register,
        data: {
          'name': name,
          'phone_number': phoneNumber,
          'password': password,
          'password_confirmation': passwordConfirmation,
          if (referralCode != null && referralCode.trim().isNotEmpty) 'referral_code': referralCode.trim(),
        },
      );
      return response.data;
    } on DioException catch (e) {
      throw e.response?.data['message'] ?? 'فشل إنشاء الحساب';
    }
  }

  Future<Map<String, dynamic>> requestOtp({
    required String phoneNumber,
    required String purpose,
  }) async {
    try {
      final response = await _dio.post(
        ApiConstants.requestOtp,
        data: {
          'phone_number': phoneNumber,
          'purpose': purpose,
        },
      );
      return response.data;
    } on DioException catch (e) {
      throw e.response?.data['message'] ?? 'حدث خطأ غير متوقع أثناء إرسال رمز التحقق';
    }
  }

  Future<Map<String, dynamic>> verifyOtp({
    required String phoneNumber,
    required String otp,
  }) async {
    try {
      final response = await _dio.post(
        ApiConstants.verifyOtp,
        data: {
          'phone_number': phoneNumber,
          'otp': otp,
        },
      );
      return response.data;
    } on DioException catch (e) {
      throw e.response?.data['message'] ?? 'فشل التحقق من الرمز';
    }
  }

  Future<Map<String, dynamic>> requestPasswordResetOtp({
    required String phoneNumber,
  }) async {
    try {
      final response = await _dio.post(
        ApiConstants.requestPasswordResetOtp,
        data: {
          'phone_number': phoneNumber,
        },
      );
      return response.data;
    } on DioException catch (e) {
      throw e.response?.data['message'] ?? 'حدث خطأ غير متوقع أثناء طلب إعادة التعيين';
    }
  }

  Future<Map<String, dynamic>> resetPassword({
    required String phoneNumber,
    required String otp,
    required String password,
    required String passwordConfirmation,
  }) async {
    try {
      final response = await _dio.post(
        ApiConstants.resetPassword,
        data: {
          'phone_number': phoneNumber,
          'otp': otp,
          'password': password,
          'password_confirmation': passwordConfirmation,
        },
      );
      return response.data;
    } on DioException catch (e) {
      throw e.response?.data['message'] ?? 'فشل إعادة تعيين كلمة المرور';
    }
  }

  Future<UserModel> updateProfile({
    String? name,
    String? email,
    String? phoneNumber,
    String? password,
    String? passwordConfirmation,
  }) async {
    try {
      final payload = <String, dynamic>{
        if (name != null && name.trim().isNotEmpty) 'name': name.trim(),
        if (email != null) 'email': email.trim(),
        if (phoneNumber != null) 'phone_number': phoneNumber.trim(),
        if (password != null && password.isNotEmpty) 'password': password,
        if (passwordConfirmation != null && passwordConfirmation.isNotEmpty) 'password_confirmation': passwordConfirmation,
      };

      final response = await _dio.patch(
        ApiConstants.profileUpdate,
        data: payload,
      );

      return UserModel.fromJson(response.data['data']);
    } on DioException catch (e) {
      throw e.response?.data['message'] ?? 'فشل تحديث الملف الشخصي';
    }
  }

  Future<Map<String, dynamic>> fetchOrders({int page = 1}) async {
    try {
      final response = await _dio.get(
        ApiConstants.profileOrders,
        queryParameters: {'page': page},
      );
      return response.data;
    } on DioException catch (e) {
      throw e.response?.data['message'] ?? 'فشل جلب الطلبات';
    }
  }

  Future<Map<String, dynamic>> fetchDiscounts({int page = 1}) async {
    try {
      final response = await _dio.get(
        ApiConstants.profileDiscounts,
        queryParameters: {'page': page},
      );
      return response.data;
    } on DioException catch (e) {
      throw e.response?.data['message'] ?? 'فشل جلب أكواد الخصم';
    }
  }

  Future<Map<String, dynamic>> fetchOrderDetails(int orderId) async {
    try {
      final response = await _dio.get('${ApiConstants.profileOrders}/$orderId');
      return response.data;
    } on DioException catch (e) {
      throw e.response?.data['message'] ?? 'فشل جلب تفاصيل الطلب';
    }
  }

  Future<Map<String, dynamic>> fetchAddresses() async {
    try {
      final response = await _dio.get('${ApiConstants.profile}/addresses');
      return response.data;
    } on DioException catch (e) {
      throw e.response?.data['message'] ?? 'فشل جلب العناوين';
    }
  }

  Future<Map<String, dynamic>> fetchFavorites() async {
    try {
      final response = await _dio.get(ApiConstants.wishlist);
      return response.data;
    } on DioException catch (e) {
      throw e.response?.data['message'] ?? 'فشل جلب المفضلة';
    }
  }

  Future<Map<String, dynamic>> sendProfilePasswordOtp() async {
    try {
      final response = await _dio.post(ApiConstants.profilePasswordSendOtp);
      return response.data;
    } on DioException catch (e) {
      throw e.response?.data['message'] ?? 'تعذر إرسال رمز التحقق';
    }
  }

  Future<Map<String, dynamic>> changeProfilePassword({
    required String oldPassword,
    required String otp,
    required String password,
    required String passwordConfirmation,
  }) async {
    try {
      final response = await _dio.post(
        ApiConstants.profilePasswordChange,
        data: {
          'old_password': oldPassword,
          'otp': otp,
          'password': password,
          'password_confirmation': passwordConfirmation,
        },
      );
      return response.data;
    } on DioException catch (e) {
      throw e.response?.data['message'] ?? 'تعذر تغيير كلمة المرور';
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
