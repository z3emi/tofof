import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../providers/auth_provider.dart';
import 'auth_countries.dart';

class RegisterScreen extends ConsumerStatefulWidget {
  const RegisterScreen({super.key});

  @override
  ConsumerState<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends ConsumerState<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameCtrl = TextEditingController();
  final _phoneCtrl = TextEditingController();
  final _passwordCtrl = TextEditingController();
  final _passwordConfirmCtrl = TextEditingController();
  final _referralCtrl = TextEditingController();

  AuthCountry _selectedCountry = authCountries.first;
  bool _showPassword = false;
  bool _showConfirmPassword = false;

  @override
  void dispose() {
    _nameCtrl.dispose();
    _phoneCtrl.dispose();
    _passwordCtrl.dispose();
    _passwordConfirmCtrl.dispose();
    _referralCtrl.dispose();
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

  Future<void> _submitRegister() async {
    if (!_formKey.currentState!.validate()) return;

    final success = await ref
        .read(authProvider.notifier)
        .registerWithPhone(
          name: _nameCtrl.text.trim(),
          phoneNumber: _fullPhone(),
          password: _passwordCtrl.text,
          passwordConfirmation: _passwordConfirmCtrl.text,
          referralCode: _referralCtrl.text.trim(),
        );

    if (!mounted) return;

    final state = ref.read(authProvider);
    if (success && state.otpRequired) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('تم إرسال OTP عبر واتساب لتأكيد الحساب')),
      );
      context.push(
        '/verify-otp',
        extra: {
          'phoneNumber': state.pendingPhone ?? _fullPhone(),
          'purpose': 'register',
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
    ).showSnackBar(SnackBar(content: Text(state.error ?? 'فشل إنشاء الحساب')));
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('إنشاء حساب')),
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                const Icon(
                  Icons.person_add_alt_1,
                  size: 80,
                  color: Color(0xFF6D0E16),
                ),
                const SizedBox(height: 20),
                const Text(
                  'نفس معلومات التسجيل في الموقع: الاسم، الهاتف، كلمة المرور، وكود الدعوة (اختياري).',
                  textAlign: TextAlign.center,
                  style: TextStyle(height: 1.5),
                ),
                const SizedBox(height: 20),
                TextFormField(
                  controller: _nameCtrl,
                  decoration: const InputDecoration(
                    labelText: 'الاسم الكامل',
                    border: OutlineInputBorder(),
                  ),
                  validator: (v) => (v == null || v.trim().isEmpty)
                      ? 'مطلوب إدخال الاسم'
                      : null,
                ),
                const SizedBox(height: 14),
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
                const SizedBox(height: 14),
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
                  validator: (v) {
                    if (v == null || v.isEmpty)
                      return 'مطلوب إدخال كلمة المرور';
                    if (v.length < 8)
                      return 'كلمة المرور يجب أن تكون 8 أحرف أو أكثر';
                    return null;
                  },
                ),
                const SizedBox(height: 14),
                TextFormField(
                  controller: _passwordConfirmCtrl,
                  obscureText: !_showConfirmPassword,
                  decoration: InputDecoration(
                    labelText: 'تأكيد كلمة المرور',
                    border: const OutlineInputBorder(),
                    suffixIcon: IconButton(
                      onPressed: () => setState(
                        () => _showConfirmPassword = !_showConfirmPassword,
                      ),
                      icon: Icon(
                        _showConfirmPassword
                            ? Icons.visibility_off
                            : Icons.visibility,
                      ),
                    ),
                  ),
                  validator: (v) => v != _passwordCtrl.text
                      ? 'كلمتا المرور غير متطابقتين'
                      : null,
                ),
                const SizedBox(height: 14),
                TextFormField(
                  controller: _referralCtrl,
                  decoration: const InputDecoration(
                    labelText: 'رمز الدعوة (اختياري)',
                    border: OutlineInputBorder(),
                  ),
                ),
                const SizedBox(height: 22),
                ElevatedButton(
                  onPressed: authState.isLoading ? null : _submitRegister,
                  child: authState.isLoading
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            color: Colors.white,
                          ),
                        )
                      : const Text('تسجيل'),
                ),
                const SizedBox(height: 8),
                TextButton(
                  onPressed: () => context.push('/login'),
                  child: const Text('لدي حساب بالفعل'),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
