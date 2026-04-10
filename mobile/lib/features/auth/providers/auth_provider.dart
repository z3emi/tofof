import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../shared/models/user_model.dart';
import '../../../../core/storage/secure_storage.dart';
import '../data/auth_repository.dart';

class AuthState {
  final UserModel? user;
  final bool isLoading;
  final String? error;

  AuthState({this.user, this.isLoading = false, this.error});

  AuthState copyWith({
    UserModel? user,
    bool? isLoading,
    String? error,
    bool clearError = false,
  }) {
    return AuthState(
      user: user ?? this.user,
      isLoading: isLoading ?? this.isLoading,
      error: clearError ? null : (error ?? this.error),
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

  Future<bool> login(String email, String password) async {
    state = state.copyWith(isLoading: true, clearError: true);
    try {
      final response = await _repository.login(email, password);
      final token = response['data']['token'] as String;
      final userJson = response['data']['user'] as Map<String, dynamic>;
      
      await _tokenStorage.saveToken(token);
      final user = UserModel.fromJson(userJson);
      
      state = state.copyWith(user: user, isLoading: false);
      return true;
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
      return false;
    }
  }

  Future<bool> register(Map<String, dynamic> data) async {
    state = state.copyWith(isLoading: true, clearError: true);
    try {
      final response = await _repository.register(data);
      final token = response['data']['token'] as String;
      final userJson = response['data']['user'] as Map<String, dynamic>;

      await _tokenStorage.saveToken(token);
      final user = UserModel.fromJson(userJson);

      state = state.copyWith(user: user, isLoading: false);
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
