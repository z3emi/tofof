import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../providers/auth_provider.dart';
import 'auth_countries.dart';

class PasswordResetScreen extends ConsumerStatefulWidget {
  const PasswordResetScreen({super.key});

  @override
  ConsumerState<PasswordResetScreen> createState() => _PasswordResetScreenState();
}

class _PasswordResetScreenState extends ConsumerState<PasswordResetScreen> {
  final _formKey = GlobalKey<FormState>();
  final _phoneCtrl = TextEditingController();
  final _otpCtrl = TextEditingController();
  final _passwordCtrl = TextEditingController();
  final _passwordConfirmCtrl = TextEditingController();

  AuthCountry _selectedCountry = authCountries.first;
  bool _showPassword = false;
  bool _showConfirmPassword = false;
  bool _otpStep = false;
  String? _pendingPhone;

  @override
  void dispose() {
    _phoneCtrl.dispose();
    _otpCtrl.dispose();
    _passwordCtrl.dispose();
    _passwordConfirmCtrl.dispose();
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
                  leading: Text(country.flagEmoji, style: const TextStyle(fontSize: 22)),
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

  Future<void> _sendOtp() async {
    if (!_formKey.currentState!.validate()) return;

    final success = await ref.read(authProvider.notifier).requestPasswordResetOtp(
          phoneNumber: _fullPhone(),
        );

    if (!mounted) return;

    if (success) {
      setState(() {
        _otpStep = true;
        _pendingPhone = _fullPhone();
      });
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('تم إرسال رمز إعادة التعيين عبر واتساب')),
      );
      return;
    }

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(ref.read(authProvider).error ?? 'تعذر إرسال الرمز')),
    );
  }

  Future<void> _resetPassword() async {
    if (_pendingPhone == null || _otpCtrl.text.trim().length != 6) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('أدخل الرمز بشكل صحيح')),
      );
      return;
    }

    if (_passwordCtrl.text != _passwordConfirmCtrl.text) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('كلمتا المرور غير متطابقتين')),
      );
      return;
    }

    final success = await ref.read(authProvider.notifier).resetPassword(
          phoneNumber: _pendingPhone!,
          otp: _otpCtrl.text.trim(),
          password: _passwordCtrl.text,
          passwordConfirmation: _passwordConfirmCtrl.text,
        );

    if (!mounted) return;

    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('تم تغيير كلمة المرور بنجاح')),
      );
      context.go('/login');
      return;
    }

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(ref.read(authProvider).error ?? 'تعذر إعادة التعيين')),
    );
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('إعادة تعيين كلمة المرور')),
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: Container(
            constraints: const BoxConstraints(maxWidth: 460),
            padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(
              color: Theme.of(context).colorScheme.surface,
              borderRadius: BorderRadius.circular(24),
              border: Border.all(color: Theme.of(context).dividerColor.withValues(alpha: 0.15)),
            ),
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  const Icon(Icons.lock_reset_rounded, size: 76, color: Color(0xFF6D0E16)),
                  const SizedBox(height: 18),
                  const Text('استعادة كلمة المرور', textAlign: TextAlign.center, style: TextStyle(fontSize: 24, fontWeight: FontWeight.w800)),
                  const SizedBox(height: 8),
                  Text(
                    _otpStep ? 'أدخل الرمز الجديد وكلمة المرور الجديدة.' : 'أدخل رقم الهاتف وسيصلك رمز التحقق عبر واتساب.',
                    textAlign: TextAlign.center,
                    style: TextStyle(color: Colors.grey.shade600, height: 1.5),
                  ),
                  const SizedBox(height: 18),
                  const Text('رقم الهاتف', style: TextStyle(fontWeight: FontWeight.w700)),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      InkWell(
                        onTap: _pickCountry,
                        borderRadius: BorderRadius.circular(12),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 14),
                          decoration: BoxDecoration(
                            border: Border.all(color: Colors.grey.shade400),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Row(
                            children: [
                              Text(_selectedCountry.flagEmoji),
                              const SizedBox(width: 6),
                              Text(_selectedCountry.dialCode, textDirection: TextDirection.ltr),
                              const SizedBox(width: 4),
                              const Icon(Icons.keyboard_arrow_down_rounded, size: 18),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: TextFormField(
                          controller: _phoneCtrl,
                          keyboardType: TextInputType.phone,
                          enabled: !_otpStep,
                          decoration: InputDecoration(
                            border: const OutlineInputBorder(),
                            hintText: _selectedCountry.dialCode == '+964' ? '7712345678' : 'Phone number',
                          ),
                          validator: (v) {
                            final value = _normalizeLocalPhone(v ?? '');
                            if (value.isEmpty) return 'مطلوب إدخال رقم الهاتف';
                            if (_selectedCountry.dialCode == '+964' && value.length != 10) {
                              return 'رقم العراق يجب أن يكون 10 أرقام';
                            }
                            return null;
                          },
                        ),
                      ),
                    ],
                  ),
                  if (_otpStep) ...[
                    const SizedBox(height: 14),
                    TextFormField(
                      controller: _otpCtrl,
                      keyboardType: TextInputType.number,
                      maxLength: 6,
                      decoration: const InputDecoration(
                        labelText: 'رمز OTP',
                        border: OutlineInputBorder(),
                      ),
                    ),
                    const SizedBox(height: 14),
                    TextFormField(
                      controller: _passwordCtrl,
                      obscureText: !_showPassword,
                      decoration: InputDecoration(
                        labelText: 'كلمة المرور الجديدة',
                        border: const OutlineInputBorder(),
                        suffixIcon: IconButton(
                          onPressed: () => setState(() => _showPassword = !_showPassword),
                          icon: Icon(_showPassword ? Icons.visibility_off : Icons.visibility),
                        ),
                      ),
                      validator: (v) {
                        if (_otpStep && (v == null || v.length < 8)) return 'كلمة المرور يجب أن تكون 8 أحرف أو أكثر';
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
                          onPressed: () => setState(() => _showConfirmPassword = !_showConfirmPassword),
                          icon: Icon(_showConfirmPassword ? Icons.visibility_off : Icons.visibility),
                        ),
                      ),
                    ),
                  ],
                  const SizedBox(height: 22),
                  ElevatedButton(
                    onPressed: authState.isLoading ? null : (_otpStep ? _resetPassword : _sendOtp),
                    child: authState.isLoading
                        ? const SizedBox(
                            width: 20,
                            height: 20,
                            child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                          )
                        : Text(_otpStep ? 'تحديث كلمة المرور' : 'إرسال رمز التحقق'),
                  ),
                  if (_otpStep) ...[
                    const SizedBox(height: 8),
                    TextButton(
                      onPressed: authState.isLoading ? null : () => setState(() => _otpStep = false),
                      child: const Text('تغيير رقم الهاتف'),
                    ),
                  ],
                  TextButton(
                    onPressed: authState.isLoading ? null : () => context.go('/login'),
                    child: const Text('العودة إلى تسجيل الدخول'),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
