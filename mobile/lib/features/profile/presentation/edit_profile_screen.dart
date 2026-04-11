import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../shared/models/user_model.dart';
import '../../auth/providers/auth_provider.dart';

class EditProfileScreen extends ConsumerStatefulWidget {
  const EditProfileScreen({super.key});

  @override
  ConsumerState<EditProfileScreen> createState() => _EditProfileScreenState();
}

class _EditProfileScreenState extends ConsumerState<EditProfileScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameCtrl = TextEditingController();
  final _emailCtrl = TextEditingController();
  final _phoneCtrl = TextEditingController();
  final _oldPasswordCtrl = TextEditingController();
  final _passwordCtrl = TextEditingController();
  final _passwordConfirmCtrl = TextEditingController();
  final _otpCtrl = TextEditingController();

  bool _showOldPassword = false;
  bool _showPassword = false;
  bool _showConfirmPassword = false;
  bool _initialized = false;
  bool _otpSent = false;

  @override
  void dispose() {
    _nameCtrl.dispose();
    _emailCtrl.dispose();
    _phoneCtrl.dispose();
    _oldPasswordCtrl.dispose();
    _passwordCtrl.dispose();
    _passwordConfirmCtrl.dispose();
    _otpCtrl.dispose();
    super.dispose();
  }

  void _ensureInitialValues(UserModel user) {
    if (_initialized) return;
    _initialized = true;
    _nameCtrl.text = user.name;
    _emailCtrl.text = user.email;
    _phoneCtrl.text = user.phoneNumber;
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;

    final success = await ref.read(authProvider.notifier).updateProfile(
          name: _nameCtrl.text.trim(),
          email: _emailCtrl.text.trim(),
          phoneNumber: _phoneCtrl.text.trim(),
        );

    if (!mounted) return;

    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('تم تحديث الملف الشخصي بنجاح')),
      );
      Navigator.of(context).pop();
      return;
    }

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(ref.read(authProvider).error ?? 'تعذر تحديث الملف الشخصي')),
    );
  }

  Future<void> _sendOtpForPasswordChange() async {
    final oldPassword = _oldPasswordCtrl.text.trim();
    if (oldPassword.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('أدخل كلمة المرور الحالية أولًا')),
      );
      return;
    }

    final success = await ref.read(authProvider.notifier).sendProfilePasswordOtp();
    if (!mounted) return;

    if (success) {
      setState(() => _otpSent = true);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('تم إرسال رمز التحقق إلى واتساب: ${_phoneCtrl.text.trim()}')),
      );
      return;
    }

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(ref.read(authProvider).error ?? 'تعذر إرسال رمز التحقق')),
    );
  }

  Future<void> _changePassword() async {
    final oldPassword = _oldPasswordCtrl.text.trim();
    final otp = _otpCtrl.text.trim();
    final newPassword = _passwordCtrl.text.trim();
    final confirmPassword = _passwordConfirmCtrl.text.trim();

    if (oldPassword.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('أدخل كلمة المرور الحالية')),
      );
      return;
    }

    if (otp.length != 6) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('أدخل رمز OTP من 6 أرقام')),
      );
      return;
    }

    if (newPassword.length < 8) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('كلمة المرور الجديدة يجب أن تكون 8 أحرف أو أكثر')),
      );
      return;
    }

    if (newPassword != confirmPassword) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('كلمتا المرور الجديدتان غير متطابقتين')),
      );
      return;
    }

    final success = await ref.read(authProvider.notifier).changeProfilePassword(
          oldPassword: oldPassword,
          otp: otp,
          password: newPassword,
          passwordConfirmation: confirmPassword,
        );

    if (!mounted) return;

    if (success) {
      _oldPasswordCtrl.clear();
      _otpCtrl.clear();
      _passwordCtrl.clear();
      _passwordConfirmCtrl.clear();
      setState(() => _otpSent = false);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('تم تغيير كلمة المرور بنجاح')),
      );
      return;
    }

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(ref.read(authProvider).error ?? 'تعذر تغيير كلمة المرور')),
    );
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authProvider);
    final user = authState.user;

    if (user != null) {
      _ensureInitialValues(user);
    }

    return Scaffold(
      appBar: AppBar(title: const Text('تعديل الملف الشخصي')),
      body: user == null
          ? const Center(child: Text('يجب تسجيل الدخول أولًا'))
          : Center(
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(24),
                child: Container(
                  constraints: const BoxConstraints(maxWidth: 520),
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
                        const CircleAvatar(radius: 38, backgroundColor: Color(0xFF6D0E16), child: Icon(Icons.person, color: Colors.white, size: 40)),
                        const SizedBox(height: 18),
                        TextFormField(
                          controller: _nameCtrl,
                          decoration: const InputDecoration(
                            labelText: 'الاسم الكامل',
                            border: OutlineInputBorder(),
                          ),
                          validator: (value) => (value == null || value.trim().isEmpty) ? 'مطلوب إدخال الاسم' : null,
                        ),
                        const SizedBox(height: 14),
                        TextFormField(
                          controller: _emailCtrl,
                          keyboardType: TextInputType.emailAddress,
                          decoration: const InputDecoration(
                            labelText: 'البريد الإلكتروني',
                            border: OutlineInputBorder(),
                          ),
                        ),
                        const SizedBox(height: 14),
                        TextFormField(
                          controller: _phoneCtrl,
                          keyboardType: TextInputType.phone,
                          readOnly: true,
                          decoration: const InputDecoration(
                            labelText: 'رقم الهاتف (للتأكيد عبر واتساب)',
                            border: OutlineInputBorder(),
                          ),
                          validator: (value) => (value == null || value.trim().isEmpty) ? 'مطلوب إدخال رقم الهاتف' : null,
                        ),
                        const SizedBox(height: 20),
                        const Divider(),
                        const SizedBox(height: 10),
                        const Text('استعادة/تغيير كلمة المرور', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800)),
                        const SizedBox(height: 8),
                        Text(
                          'لأمان الحساب: أدخل كلمة المرور الحالية، ثم اطلب رمز OTP وسيتم إرساله إلى نفس رقم الهاتف المسجل على واتساب.',
                          style: TextStyle(color: Colors.grey.shade700, height: 1.5),
                        ),
                        const SizedBox(height: 14),
                        TextFormField(
                          controller: _oldPasswordCtrl,
                          obscureText: !_showOldPassword,
                          decoration: InputDecoration(
                            labelText: 'كلمة المرور الحالية',
                            border: const OutlineInputBorder(),
                            suffixIcon: IconButton(
                              onPressed: () => setState(() => _showOldPassword = !_showOldPassword),
                              icon: Icon(_showOldPassword ? Icons.visibility_off : Icons.visibility),
                            ),
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
                        ),
                        const SizedBox(height: 14),
                        TextFormField(
                          controller: _passwordConfirmCtrl,
                          obscureText: !_showConfirmPassword,
                          decoration: InputDecoration(
                            labelText: 'تأكيد كلمة المرور الجديدة',
                            border: const OutlineInputBorder(),
                            suffixIcon: IconButton(
                              onPressed: () => setState(() => _showConfirmPassword = !_showConfirmPassword),
                              icon: Icon(_showConfirmPassword ? Icons.visibility_off : Icons.visibility),
                            ),
                          ),
                        ),
                        const SizedBox(height: 14),
                        if (_otpSent)
                          TextFormField(
                            controller: _otpCtrl,
                            keyboardType: TextInputType.number,
                            maxLength: 6,
                            decoration: const InputDecoration(
                              labelText: 'رمز OTP من واتساب',
                              border: OutlineInputBorder(),
                            ),
                          ),
                        const SizedBox(height: 8),
                        OutlinedButton.icon(
                          onPressed: authState.isLoading ? null : _sendOtpForPasswordChange,
                          icon: const Icon(Icons.mark_chat_unread_outlined),
                          label: Text(_otpSent ? 'إعادة إرسال رمز التحقق' : 'إرسال رمز التحقق إلى واتساب'),
                        ),
                        const SizedBox(height: 8),
                        ElevatedButton(
                          onPressed: authState.isLoading || !_otpSent ? null : _changePassword,
                          child: const Text('تغيير كلمة المرور'),
                        ),
                        const SizedBox(height: 20),
                        ElevatedButton(
                          onPressed: authState.isLoading ? null : _save,
                          child: authState.isLoading
                              ? const SizedBox(
                                  width: 20,
                                  height: 20,
                                  child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                                )
                              : const Text('حفظ بيانات الملف الشخصي'),
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
