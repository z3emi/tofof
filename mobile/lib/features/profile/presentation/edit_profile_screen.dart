import 'dart:ui';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:skeletonizer/skeletonizer.dart';

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

    final success = await ref
        .read(authProvider.notifier)
        .updateProfile(
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
      SnackBar(
        content: Text(
          ref.read(authProvider).error ?? 'تعذر تحديث الملف الشخصي',
        ),
      ),
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

    final success = await ref
        .read(authProvider.notifier)
        .sendProfilePasswordOtp();
    if (!mounted) return;

    if (success) {
      setState(() => _otpSent = true);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            'تم إرسال رمز التحقق إلى واتساب: ${_phoneCtrl.text.trim()}',
          ),
        ),
      );
      return;
    }

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(ref.read(authProvider).error ?? 'تعذر إرسال رمز التحقق'),
      ),
    );
  }

  Future<void> _changePassword() async {
    final oldPassword = _oldPasswordCtrl.text.trim();
    final otp = _otpCtrl.text.trim();
    final newPassword = _passwordCtrl.text.trim();
    final confirmPassword = _passwordConfirmCtrl.text.trim();

    if (oldPassword.isEmpty) {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text('أدخل كلمة المرور الحالية')));
      return;
    }

    if (otp.length != 6) {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text('أدخل رمز OTP من 6 أرقام')));
      return;
    }

    if (newPassword.length < 8) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('كلمة المرور الجديدة يجب أن تكون 8 أحرف أو أكثر'),
        ),
      );
      return;
    }

    if (newPassword != confirmPassword) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('كلمتا المرور الجديدتان غير متطابقتين')),
      );
      return;
    }

    final success = await ref
        .read(authProvider.notifier)
        .changeProfilePassword(
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
      SnackBar(
        content: Text(ref.read(authProvider).error ?? 'تعذر تغيير كلمة المرور'),
      ),
    );
  }

  Widget _buildSkeletonContent(OutlineInputBorder inputBorder) {
    return Center(
      child: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Container(
          constraints: const BoxConstraints(maxWidth: 520),
          child: ClipRRect(
            borderRadius: BorderRadius.circular(28),
            child: BackdropFilter(
              filter: ImageFilter.blur(sigmaX: 18, sigmaY: 18),
              child: Container(
                padding: const EdgeInsets.all(24),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.62),
                  borderRadius: BorderRadius.circular(28),
                  border: Border.all(
                    color: Colors.white.withValues(alpha: 0.8),
                  ),
                ),
                child: Skeletonizer(
                  enabled: true,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      const CircleAvatar(radius: 38, child: Icon(Icons.person)),
                      const SizedBox(height: 18),
                      TextFormField(
                        enabled: false,
                        initialValue: 'loading',
                        decoration: InputDecoration(
                          labelText: 'الاسم الكامل',
                          filled: true,
                          fillColor: Colors.white.withValues(alpha: 0.6),
                          border: inputBorder,
                          enabledBorder: inputBorder,
                        ),
                      ),
                      const SizedBox(height: 14),
                      TextFormField(
                        enabled: false,
                        initialValue: 'loading@email.com',
                        decoration: InputDecoration(
                          labelText: 'البريد الإلكتروني',
                          filled: true,
                          fillColor: Colors.white.withValues(alpha: 0.6),
                          border: inputBorder,
                          enabledBorder: inputBorder,
                        ),
                      ),
                      const SizedBox(height: 14),
                      TextFormField(
                        enabled: false,
                        initialValue: '07xxxxxxxxx',
                        decoration: InputDecoration(
                          labelText: 'رقم الهاتف (للتأكيد عبر واتساب)',
                          filled: true,
                          fillColor: Colors.white.withValues(alpha: 0.48),
                          border: inputBorder,
                          enabledBorder: inputBorder,
                        ),
                      ),
                      const SizedBox(height: 20),
                      Divider(color: Colors.grey.shade300),
                      const SizedBox(height: 10),
                      const Text(
                        'استعادة/تغيير كلمة المرور',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        'لأمان الحساب: أدخل كلمة المرور الحالية، ثم اطلب رمز OTP وسيتم إرساله إلى نفس رقم الهاتف المسجل على واتساب.',
                        style: TextStyle(
                          color: Colors.grey.shade700,
                          height: 1.5,
                        ),
                      ),
                      const SizedBox(height: 14),
                      TextFormField(
                        enabled: false,
                        initialValue: '********',
                        decoration: InputDecoration(
                          labelText: 'كلمة المرور الحالية',
                          border: inputBorder,
                          enabledBorder: inputBorder,
                          filled: true,
                          fillColor: Colors.white.withValues(alpha: 0.6),
                        ),
                      ),
                      const SizedBox(height: 14),
                      TextFormField(
                        enabled: false,
                        initialValue: '********',
                        decoration: InputDecoration(
                          labelText: 'كلمة المرور الجديدة',
                          border: inputBorder,
                          enabledBorder: inputBorder,
                          filled: true,
                          fillColor: Colors.white.withValues(alpha: 0.6),
                        ),
                      ),
                      const SizedBox(height: 14),
                      TextFormField(
                        enabled: false,
                        initialValue: '********',
                        decoration: InputDecoration(
                          labelText: 'تأكيد كلمة المرور الجديدة',
                          border: inputBorder,
                          enabledBorder: inputBorder,
                          filled: true,
                          fillColor: Colors.white.withValues(alpha: 0.6),
                        ),
                      ),
                      const SizedBox(height: 16),
                      OutlinedButton.icon(
                        onPressed: null,
                        icon: const Icon(Icons.mark_chat_unread_outlined),
                        label: const Text('إرسال رمز التحقق إلى واتساب'),
                      ),
                      const SizedBox(height: 8),
                      ElevatedButton(
                        onPressed: null,
                        child: const Text('تغيير كلمة المرور'),
                      ),
                      const SizedBox(height: 20),
                      ElevatedButton(
                        onPressed: null,
                        child: const Text('حفظ بيانات الملف الشخصي'),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authProvider);
    final user = authState.user;

    if (user != null) {
      _ensureInitialValues(user);
    }

    final inputBorder = OutlineInputBorder(
      borderRadius: BorderRadius.circular(16),
      borderSide: BorderSide(color: Colors.grey.shade300),
    );
    final isInitialLoading = authState.isLoading && user == null;

    return Scaffold(
      appBar: AppBar(title: const Text('تعديل الملف الشخصي')),
      body: Stack(
        fit: StackFit.expand,
        children: [
          const DecoratedBox(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
                colors: [Color(0xFFF8F2F1), Color(0xFFFDFCFB)],
              ),
            ),
          ),
          Positioned(
            top: -90,
            left: -60,
            child: _BlurBubble(
              size: 230,
              color: const Color(0xFF6D0E16).withValues(alpha: 0.12),
            ),
          ),
          Positioned(
            right: -50,
            bottom: 80,
            child: _BlurBubble(
              size: 180,
              color: const Color(0xFFD59E06).withValues(alpha: 0.14),
            ),
          ),
          if (isInitialLoading)
            _buildSkeletonContent(inputBorder)
          else if (user == null)
            const Center(child: Text('يجب تسجيل الدخول أولًا'))
          else
            Center(
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(24),
                child: Container(
                  constraints: const BoxConstraints(maxWidth: 520),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(28),
                    child: BackdropFilter(
                      filter: ImageFilter.blur(sigmaX: 18, sigmaY: 18),
                      child: Container(
                        padding: const EdgeInsets.all(24),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.62),
                          borderRadius: BorderRadius.circular(28),
                          border: Border.all(
                            color: Colors.white.withValues(alpha: 0.8),
                          ),
                          boxShadow: [
                            BoxShadow(
                              color: const Color(
                                0xFF6D0E16,
                              ).withValues(alpha: 0.08),
                              blurRadius: 28,
                              offset: const Offset(0, 14),
                            ),
                          ],
                        ),
                        child: Form(
                          key: _formKey,
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.stretch,
                            children: [
                              const CircleAvatar(
                                radius: 38,
                                backgroundColor: Color(0xFF6D0E16),
                                child: Icon(
                                  Icons.person,
                                  color: Colors.white,
                                  size: 40,
                                ),
                              ),
                              const SizedBox(height: 18),
                              TextFormField(
                                controller: _nameCtrl,
                                decoration: InputDecoration(
                                  labelText: 'الاسم الكامل',
                                  filled: true,
                                  fillColor: Colors.white.withValues(
                                    alpha: 0.6,
                                  ),
                                  border: inputBorder,
                                  enabledBorder: inputBorder,
                                ),
                                validator: (value) =>
                                    (value == null || value.trim().isEmpty)
                                    ? 'مطلوب إدخال الاسم'
                                    : null,
                              ),
                              const SizedBox(height: 14),
                              TextFormField(
                                controller: _emailCtrl,
                                keyboardType: TextInputType.emailAddress,
                                decoration: InputDecoration(
                                  labelText: 'البريد الإلكتروني',
                                  filled: true,
                                  fillColor: Colors.white.withValues(
                                    alpha: 0.6,
                                  ),
                                  border: inputBorder,
                                  enabledBorder: inputBorder,
                                ),
                              ),
                              const SizedBox(height: 14),
                              TextFormField(
                                controller: _phoneCtrl,
                                keyboardType: TextInputType.phone,
                                readOnly: true,
                                decoration: InputDecoration(
                                  labelText: 'رقم الهاتف (للتأكيد عبر واتساب)',
                                  filled: true,
                                  fillColor: Colors.white.withValues(
                                    alpha: 0.48,
                                  ),
                                  border: inputBorder,
                                  enabledBorder: inputBorder,
                                ),
                                validator: (value) =>
                                    (value == null || value.trim().isEmpty)
                                    ? 'مطلوب إدخال رقم الهاتف'
                                    : null,
                              ),
                              const SizedBox(height: 20),
                              Divider(color: Colors.grey.shade300),
                              const SizedBox(height: 10),
                              const Text(
                                'استعادة/تغيير كلمة المرور',
                                style: TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.w800,
                                ),
                              ),
                              const SizedBox(height: 8),
                              Text(
                                'لأمان الحساب: أدخل كلمة المرور الحالية، ثم اطلب رمز OTP وسيتم إرساله إلى نفس رقم الهاتف المسجل على واتساب.',
                                style: TextStyle(
                                  color: Colors.grey.shade700,
                                  height: 1.5,
                                ),
                              ),
                              const SizedBox(height: 14),
                              TextFormField(
                                controller: _oldPasswordCtrl,
                                obscureText: !_showOldPassword,
                                decoration: InputDecoration(
                                  labelText: 'كلمة المرور الحالية',
                                  border: inputBorder,
                                  enabledBorder: inputBorder,
                                  filled: true,
                                  fillColor: Colors.white.withValues(
                                    alpha: 0.6,
                                  ),
                                  suffixIcon: IconButton(
                                    onPressed: () => setState(
                                      () =>
                                          _showOldPassword = !_showOldPassword,
                                    ),
                                    icon: Icon(
                                      _showOldPassword
                                          ? Icons.visibility_off
                                          : Icons.visibility,
                                    ),
                                  ),
                                ),
                              ),
                              const SizedBox(height: 14),
                              TextFormField(
                                controller: _passwordCtrl,
                                obscureText: !_showPassword,
                                decoration: InputDecoration(
                                  labelText: 'كلمة المرور الجديدة',
                                  border: inputBorder,
                                  enabledBorder: inputBorder,
                                  filled: true,
                                  fillColor: Colors.white.withValues(
                                    alpha: 0.6,
                                  ),
                                  suffixIcon: IconButton(
                                    onPressed: () => setState(
                                      () => _showPassword = !_showPassword,
                                    ),
                                    icon: Icon(
                                      _showPassword
                                          ? Icons.visibility_off
                                          : Icons.visibility,
                                    ),
                                  ),
                                ),
                              ),
                              const SizedBox(height: 14),
                              TextFormField(
                                controller: _passwordConfirmCtrl,
                                obscureText: !_showConfirmPassword,
                                decoration: InputDecoration(
                                  labelText: 'تأكيد كلمة المرور الجديدة',
                                  border: inputBorder,
                                  enabledBorder: inputBorder,
                                  filled: true,
                                  fillColor: Colors.white.withValues(
                                    alpha: 0.6,
                                  ),
                                  suffixIcon: IconButton(
                                    onPressed: () => setState(
                                      () => _showConfirmPassword =
                                          !_showConfirmPassword,
                                    ),
                                    icon: Icon(
                                      _showConfirmPassword
                                          ? Icons.visibility_off
                                          : Icons.visibility,
                                    ),
                                  ),
                                ),
                              ),
                              const SizedBox(height: 14),
                              if (_otpSent)
                                TextFormField(
                                  controller: _otpCtrl,
                                  keyboardType: TextInputType.number,
                                  maxLength: 6,
                                  decoration: InputDecoration(
                                    labelText: 'رمز OTP من واتساب',
                                    border: inputBorder,
                                    enabledBorder: inputBorder,
                                    filled: true,
                                    fillColor: Colors.white.withValues(
                                      alpha: 0.6,
                                    ),
                                  ),
                                ),
                              const SizedBox(height: 8),
                              OutlinedButton.icon(
                                onPressed: authState.isLoading
                                    ? null
                                    : _sendOtpForPasswordChange,
                                icon: const Icon(
                                  Icons.mark_chat_unread_outlined,
                                ),
                                style: OutlinedButton.styleFrom(
                                  side: BorderSide(
                                    color: const Color(
                                      0xFF6D0E16,
                                    ).withValues(alpha: 0.35),
                                  ),
                                  foregroundColor: const Color(0xFF6D0E16),
                                ),
                                label: Text(
                                  _otpSent
                                      ? 'إعادة إرسال رمز التحقق'
                                      : 'إرسال رمز التحقق إلى واتساب',
                                ),
                              ),
                              const SizedBox(height: 8),
                              ElevatedButton(
                                onPressed: authState.isLoading || !_otpSent
                                    ? null
                                    : _changePassword,
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: const Color(0xFF6D0E16),
                                  foregroundColor: Colors.white,
                                ),
                                child: const Text('تغيير كلمة المرور'),
                              ),
                              const SizedBox(height: 20),
                              ElevatedButton(
                                onPressed: authState.isLoading ? null : _save,
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: const Color(0xFF6D0E16),
                                  foregroundColor: Colors.white,
                                  minimumSize: const Size.fromHeight(48),
                                ),
                                child: authState.isLoading
                                    ? const SizedBox(
                                        width: 20,
                                        height: 20,
                                        child: CircularProgressIndicator(
                                          strokeWidth: 2,
                                          color: Colors.white,
                                        ),
                                      )
                                    : const Text('حفظ بيانات الملف الشخصي'),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }
}

class _BlurBubble extends StatelessWidget {
  final double size;
  final Color color;

  const _BlurBubble({required this.size, required this.color});

  @override
  Widget build(BuildContext context) {
    return IgnorePointer(
      child: ImageFiltered(
        imageFilter: ImageFilter.blur(sigmaX: 36, sigmaY: 36),
        child: Container(
          width: size,
          height: size,
          decoration: BoxDecoration(color: color, shape: BoxShape.circle),
        ),
      ),
    );
  }
}
