import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../shared/models/user_model.dart';
import '../../../../core/storage/secure_storage.dart';
import '../data/auth_repository.dart';

class AuthState {
  final UserModel? user;
  final bool isLoading;
  final String? error;
  final bool otpRequired;
  final String? pendingPhone;

  AuthState({
    this.user,
    this.isLoading = false,
    this.error,
    this.otpRequired = false,
    this.pendingPhone,
  });

  AuthState copyWith({
    UserModel? user,
    bool? isLoading,
    String? error,
    bool? otpRequired,
    String? pendingPhone,
    bool clearError = false,
    bool clearOtpState = false,
  }) {
    return AuthState(
      user: user ?? this.user,
      isLoading: isLoading ?? this.isLoading,
      error: clearError ? null : (error ?? this.error),
      otpRequired: clearOtpState ? false : (otpRequired ?? this.otpRequired),
      pendingPhone: clearOtpState ? null : (pendingPhone ?? this.pendingPhone),
    );
  }

  bool get isAuthenticated => user != null;
}

class AuthNotifier extends Notifier<AuthState> {
  @override
  AuthState build() {
    _checkInitialAuth();
    return AuthState();
  }

  AuthRepository get _repository => ref.read(authRepositoryProvider);
  TokenStorage get _tokenStorage => ref.read(tokenStorageProvider);

  Future<void> _checkInitialAuth() async {
    final token = await _tokenStorage.getToken();
    if (token != null) {
      state = state.copyWith(isLoading: true);
      try {
        final user = await _repository.fetchMe();
        state = state.copyWith(user: user, isLoading: false);
      } catch (e) {
        await _tokenStorage.clearToken();
        state = state.copyWith(isLoading: false, error: e.toString());
      }
    }
  }

  Future<bool> loginWithPhone({required String phoneNumber, required String password}) async {
    state = state.copyWith(isLoading: true, clearError: true);
    try {
      final response = await _repository.login(phoneNumber: phoneNumber, password: password);
      final data = response['data'] as Map<String, dynamic>? ?? <String, dynamic>{};
      final otpRequired = data['otp_required'] == true;

      if (otpRequired) {
        final pendingPhone = (data['phone_number'] as String?) ?? phoneNumber;
        state = state.copyWith(
          isLoading: false,
          otpRequired: true,
          pendingPhone: pendingPhone,
        );
        return true;
      }

      final token = data['token'] as String;
      final userJson = data['user'] as Map<String, dynamic>;

      await _tokenStorage.saveToken(token);
      state = state.copyWith(
        user: UserModel.fromJson(userJson),
        isLoading: false,
        clearOtpState: true,
      );
      return true;
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString(), clearOtpState: true);
      return false;
    }
  }

  Future<bool> registerWithPhone({
    required String name,
    required String phoneNumber,
    required String password,
    required String passwordConfirmation,
    String? referralCode,
  }) async {
    state = state.copyWith(isLoading: true, clearError: true);
    try {
      final response = await _repository.register(
        name: name,
        phoneNumber: phoneNumber,
        password: password,
        passwordConfirmation: passwordConfirmation,
        referralCode: referralCode,
      );

      final data = response['data'] as Map<String, dynamic>? ?? <String, dynamic>{};
      final otpRequired = data['otp_required'] == true;

      if (otpRequired) {
        final pendingPhone = (data['phone_number'] as String?) ?? phoneNumber;
        state = state.copyWith(
          isLoading: false,
          otpRequired: true,
          pendingPhone: pendingPhone,
        );
        return true;
      }

      final token = data['token'] as String;
      final userJson = data['user'] as Map<String, dynamic>;

      await _tokenStorage.saveToken(token);
      state = state.copyWith(
        user: UserModel.fromJson(userJson),
        isLoading: false,
        clearOtpState: true,
      );
      return true;
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString(), clearOtpState: true);
      return false;
    }
  }

  Future<bool> resendOtp({required String phoneNumber, required String purpose}) async {
    state = state.copyWith(isLoading: true, clearError: true);
    try {
      await _repository.requestOtp(phoneNumber: phoneNumber, purpose: purpose);
      state = state.copyWith(isLoading: false);
      return true;
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
      return false;
    }
  }

  Future<bool> verifyOtp({required String phoneNumber, required String otp}) async {
    state = state.copyWith(isLoading: true, clearError: true);
    try {
      final response = await _repository.verifyOtp(phoneNumber: phoneNumber, otp: otp);
      final token = response['data']['token'] as String;
      final userJson = response['data']['user'] as Map<String, dynamic>;

      await _tokenStorage.saveToken(token);
      final user = UserModel.fromJson(userJson);

      state = state.copyWith(user: user, isLoading: false, clearOtpState: true);
      return true;
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
      return false;
    }
  }

  Future<bool> requestPasswordResetOtp({required String phoneNumber}) async {
    state = state.copyWith(isLoading: true, clearError: true);
    try {
      await _repository.requestPasswordResetOtp(phoneNumber: phoneNumber);
      state = state.copyWith(isLoading: false);
      return true;
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
      return false;
    }
  }

  Future<bool> resetPassword({
    required String phoneNumber,
    required String otp,
    required String password,
    required String passwordConfirmation,
  }) async {
    state = state.copyWith(isLoading: true, clearError: true);
    try {
      await _repository.resetPassword(
        phoneNumber: phoneNumber,
        otp: otp,
        password: password,
        passwordConfirmation: passwordConfirmation,
      );
      state = state.copyWith(isLoading: false);
      return true;
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
      return false;
    }
  }

  Future<bool> updateProfile({
    String? name,
    String? email,
    String? phoneNumber,
    String? password,
    String? passwordConfirmation,
  }) async {
    state = state.copyWith(isLoading: true, clearError: true);
    try {
      final user = await _repository.updateProfile(
        name: name,
        email: email,
        phoneNumber: phoneNumber,
        password: password,
        passwordConfirmation: passwordConfirmation,
      );

      state = state.copyWith(user: user, isLoading: false);
      return true;
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
      return false;
    }
  }

  Future<bool> sendProfilePasswordOtp() async {
    state = state.copyWith(isLoading: true, clearError: true);
    try {
      await _repository.sendProfilePasswordOtp();
      state = state.copyWith(isLoading: false);
      return true;
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
      return false;
    }
  }

  Future<bool> changeProfilePassword({
    required String oldPassword,
    required String otp,
    required String password,
    required String passwordConfirmation,
  }) async {
    state = state.copyWith(isLoading: true, clearError: true);
    try {
      await _repository.changeProfilePassword(
        oldPassword: oldPassword,
        otp: otp,
        password: password,
        passwordConfirmation: passwordConfirmation,
      );
      state = state.copyWith(isLoading: false);
      return true;
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
      return false;
    }
  }

  Future<void> logout() async {
    state = state.copyWith(isLoading: true);
    await _repository.logout();
    await _tokenStorage.clearToken();
    state = AuthState(); // Reset
  }
}

final authProvider = NotifierProvider<AuthNotifier, AuthState>(() {
  return AuthNotifier();
});
