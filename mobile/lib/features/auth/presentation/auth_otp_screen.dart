import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../providers/auth_provider.dart';

class AuthOtpScreen extends ConsumerStatefulWidget {
  const AuthOtpScreen({super.key});

  @override
  ConsumerState<AuthOtpScreen> createState() => _AuthOtpScreenState();
}

class _AuthOtpScreenState extends ConsumerState<AuthOtpScreen> {
  final _formKey = GlobalKey<FormState>();
  final _otpCtrl = TextEditingController();
  String? _phoneNumber;
  String _purpose = 'login';

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final extra = GoRouterState.of(context).extra;
    if (extra is Map) {
      _phoneNumber ??= extra['phoneNumber']?.toString();
      _purpose = extra['purpose']?.toString() ?? _purpose;
    }
  }

  @override
  void dispose() {
    _otpCtrl.dispose();
    super.dispose();
  }

  String get _title {
    switch (_purpose) {
      case 'register':
        return 'تأكيد الحساب';
      default:
        return 'تأكيد تسجيل الدخول';
    }
  }

  String get _subtitle {
    switch (_purpose) {
      case 'register':
        return 'أدخل رمز التحقق المرسل عبر واتساب لإكمال إنشاء الحساب.';
      default:
        return 'أدخل رمز التحقق المرسل عبر واتساب لإكمال تسجيل الدخول.';
    }
  }

  Future<void> _verify() async {
    if (!_formKey.currentState!.validate() || _phoneNumber == null) return;

    final success = await ref.read(authProvider.notifier).verifyOtp(
          phoneNumber: _phoneNumber!,
          otp: _otpCtrl.text.trim(),
        );

    if (!mounted) return;

    if (success) {
      context.go('/');
      return;
    }

    final error = ref.read(authProvider).error;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(error ?? 'فشل تأكيد الرمز')),
    );
  }

  Future<void> _resend() async {
    if (_phoneNumber == null) return;

    final success = await ref.read(authProvider.notifier).resendOtp(
          phoneNumber: _phoneNumber!,
          purpose: _purpose,
        );

    if (!mounted) return;

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(success ? 'تمت إعادة إرسال الرمز' : (ref.read(authProvider).error ?? 'تعذر إعادة الإرسال'))),
    );
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authProvider);

    return Scaffold(
      appBar: AppBar(title: Text(_title)),
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
                  const Icon(Icons.verified_outlined, size: 80, color: Color(0xFF6D0E16)),
                  const SizedBox(height: 18),
                  Text(
                    _title,
                    textAlign: TextAlign.center,
                    style: const TextStyle(fontSize: 24, fontWeight: FontWeight.w800),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    _subtitle,
                    textAlign: TextAlign.center,
                    style: TextStyle(color: Colors.grey.shade600, height: 1.5),
                  ),
                  const SizedBox(height: 18),
                  if (_phoneNumber != null)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      decoration: BoxDecoration(
                        color: Colors.grey.shade100,
                        borderRadius: BorderRadius.circular(14),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const Icon(Icons.phone_android_outlined, size: 18),
                          const SizedBox(width: 8),
                          Text(_phoneNumber!, textDirection: TextDirection.ltr),
                        ],
                      ),
                    ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: _otpCtrl,
                    keyboardType: TextInputType.number,
                    maxLength: 6,
                    decoration: const InputDecoration(
                      labelText: 'رمز OTP',
                      border: OutlineInputBorder(),
                    ),
                    validator: (value) {
                      if (value == null || value.trim().length != 6) {
                        return 'أدخل رمزًا من 6 أرقام';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 8),
                  ElevatedButton(
                    onPressed: authState.isLoading ? null : _verify,
                    child: authState.isLoading
                        ? const SizedBox(
                            width: 20,
                            height: 20,
                            child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                          )
                        : const Text('تأكيد'),
                  ),
                  const SizedBox(height: 8),
                  TextButton(
                    onPressed: authState.isLoading ? null : _resend,
                    child: const Text('إعادة إرسال الرمز'),
                  ),
                  TextButton(
                    onPressed: authState.isLoading ? null : () => context.pop(),
                    child: const Text('رجوع'),
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
