import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../providers/auth_provider.dart';
import 'auth_countries.dart';

class LoginScreen extends ConsumerStatefulWidget {
  const LoginScreen({super.key});

  @override
  ConsumerState<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends ConsumerState<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _phoneCtrl = TextEditingController();
  final _passwordCtrl = TextEditingController();

  AuthCountry _selectedCountry = authCountries.first;
  bool _showPassword = false;

  @override
  void dispose() {
    _phoneCtrl.dispose();
    _passwordCtrl.dispose();
    super.dispose();
  }

  String _normalizeLocalPhone(String value) {
    var digits = value.replaceAll(RegExp(r'\D+'), '');
    if (_selectedCountry.dialCode == '+964' && digits.startsWith('0')) {
      digits = digits.substring(1);
    }
    return digits;
  }

  String _fullPhone() {
    return '${_selectedCountry.dialDigits}${_normalizeLocalPhone(_phoneCtrl.text.trim())}';
  }

  Future<void> _pickCountry() async {
    final selected = await showModalBottomSheet<AuthCountry>(
      context: context,
      showDragHandle: true,
      isScrollControlled: true,
      builder: (context) {
        return DraggableScrollableSheet(
          expand: false,
          initialChildSize: 0.7,
          minChildSize: 0.4,
          maxChildSize: 0.9,
          builder: (context, controller) {
            return ListView.separated(
              controller: controller,
              itemCount: authCountries.length,
              separatorBuilder: (_, __) => const Divider(height: 1),
              itemBuilder: (context, index) {
                final country = authCountries[index];
                return ListTile(
                  leading: Text(
                    country.flagEmoji,
                    style: const TextStyle(fontSize: 22),
                  ),
                  title: Text(country.nameAr),
                  trailing: Text(country.dialCode),
                  onTap: () => Navigator.of(context).pop(country),
                );
              },
            );
          },
        );
      },
    );

    if (selected != null) {
      setState(() => _selectedCountry = selected);
    }
  }

  Future<void> _submitLogin() async {
    if (!_formKey.currentState!.validate()) return;

    final success = await ref
        .read(authProvider.notifier)
        .loginWithPhone(
          phoneNumber: _fullPhone(),
          password: _passwordCtrl.text,
        );

    if (!mounted) return;

    final state = ref.read(authProvider);
    if (success && state.otpRequired) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('الحساب يحتاج تأكيد واتساب. تم إرسال رمز OTP.'),
        ),
      );
      context.push(
        '/verify-otp',
        extra: {
          'phoneNumber': state.pendingPhone ?? _fullPhone(),
          'purpose': 'login',
        },
      );
      return;
    }

    if (success) {
      context.go('/');
      return;
    }

    ScaffoldMessenger.of(
      context,
    ).showSnackBar(SnackBar(content: Text(state.error ?? 'فشل تسجيل الدخول')));
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('تسجيل الدخول')),
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                const Icon(
                  Icons.login_rounded,
                  size: 80,
                  color: Color(0xFF6D0E16),
                ),
                const SizedBox(height: 20),
                const Text(
                  'سجل الدخول بنفس طريقة الموقع: رقم الهاتف + الدولة + كلمة المرور.',
                  textAlign: TextAlign.center,
                  style: TextStyle(height: 1.5),
                ),
                const SizedBox(height: 20),
                const Text(
                  'رقم الهاتف',
                  style: TextStyle(fontWeight: FontWeight.w700),
                ),
                const SizedBox(height: 8),
                Row(
                  children: [
                    InkWell(
                      onTap: _pickCountry,
                      borderRadius: BorderRadius.circular(12),
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 10,
                          vertical: 14,
                        ),
                        decoration: BoxDecoration(
                          border: Border.all(color: Colors.grey.shade400),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Row(
                          children: [
                            Text(_selectedCountry.flagEmoji),
                            const SizedBox(width: 6),
                            Text(
                              _selectedCountry.dialCode,
                              textDirection: TextDirection.ltr,
                            ),
                            const SizedBox(width: 4),
                            const Icon(
                              Icons.keyboard_arrow_down_rounded,
                              size: 18,
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: TextFormField(
                        controller: _phoneCtrl,
                        keyboardType: TextInputType.phone,
                        decoration: InputDecoration(
                          border: const OutlineInputBorder(),
                          hintText: _selectedCountry.dialCode == '+964'
                              ? '7712345678'
                              : 'Phone number',
                        ),
                        validator: (v) {
                          final value = _normalizeLocalPhone(v ?? '');
                          if (value.isEmpty) return 'مطلوب إدخال رقم الهاتف';
                          if (_selectedCountry.dialCode == '+964' &&
                              value.length != 10) {
                            return 'رقم العراق يجب أن يكون 10 أرقام';
                          }
                          return null;
                        },
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                TextFormField(
                  controller: _passwordCtrl,
                  obscureText: !_showPassword,
                  decoration: InputDecoration(
                    labelText: 'كلمة المرور',
                    border: const OutlineInputBorder(),
                    suffixIcon: IconButton(
                      onPressed: () =>
                          setState(() => _showPassword = !_showPassword),
                      icon: Icon(
                        _showPassword ? Icons.visibility_off : Icons.visibility,
                      ),
                    ),
                  ),
                  validator: (v) => (v == null || v.isEmpty)
                      ? 'مطلوب إدخال كلمة المرور'
                      : null,
                ),
                const SizedBox(height: 22),
                ElevatedButton(
                  onPressed: authState.isLoading ? null : _submitLogin,
                  child: authState.isLoading
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            color: Colors.white,
                          ),
                        )
                      : const Text('دخول'),
                ),
                const SizedBox(height: 8),
                TextButton(
                  onPressed: authState.isLoading
                      ? null
                      : () => context.push('/reset-password'),
                  child: const Text('نسيت كلمة المرور؟'),
                ),
                const SizedBox(height: 2),
                TextButton(
                  onPressed: () => context.push('/register'),
                  child: const Text('إنشاء حساب جديد'),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
