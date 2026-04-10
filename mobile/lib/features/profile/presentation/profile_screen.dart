import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../auth/providers/auth_provider.dart';

class IsDarkMode extends Notifier<bool> {
  @override
  bool build() => false;
  void toggle(bool value) => state = value;
}
final isDarkModeProvider = NotifierProvider<IsDarkMode, bool>(() => IsDarkMode());

class ProfileScreen extends ConsumerWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final authState = ref.watch(authProvider);
    final user = authState.user;
    final isDarkMode = ref.watch(isDarkModeProvider);

    return Scaffold(
      body: SingleChildScrollView(
        child: Column(
          children: [
            // Header Area (Login/Register or User Detail)
            if (user == null)
              Container(
                padding: const EdgeInsets.all(24),
                decoration: const BoxDecoration(
                  color: Color(0xFF6D0E16),
                  borderRadius: BorderRadius.only(bottomLeft: Radius.circular(30), bottomRight: Radius.circular(30)),
                ),
                child: Column(
                  children: [
                    const Icon(Icons.account_circle, size: 80, color: Colors.white70),
                    const SizedBox(height: 16),
                    const Text(
                      'أهلاً بك في متجر رفوف',
                      style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Colors.white),
                    ),
                    const SizedBox(height: 16),
                    Row(
                      children: [
                        Expanded(
                          child: ElevatedButton(
                            style: ElevatedButton.styleFrom(
                              backgroundColor: Colors.white,
                              foregroundColor: const Color(0xFF6D0E16),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            ),
                            onPressed: () => context.push('/login'),
                            child: const Text('تسجيل الدخول', style: TextStyle(fontWeight: FontWeight.bold)),
                          ),
                        ),
                        const SizedBox(width: 16),
                        Expanded(
                          child: OutlinedButton(
                            style: OutlinedButton.styleFrom(
                              foregroundColor: Colors.white,
                              side: const BorderSide(color: Colors.white),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            ),
                            onPressed: () => context.push('/register'),
                            child: const Text('إنشاء حساب'),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              )
            else
              Container(
                padding: const EdgeInsets.all(24),
                decoration: const BoxDecoration(
                  color: Color(0xFF6D0E16),
                  borderRadius: BorderRadius.only(bottomLeft: Radius.circular(30), bottomRight: Radius.circular(30)),
                ),
                child: Row(
                  children: [
                    CircleAvatar(
                      radius: 35,
                      backgroundColor: Colors.white,
                      backgroundImage: user.avatar != null && user.avatar!.isNotEmpty ? NetworkImage(user.avatar!) : null,
                      child: (user.avatar == null || user.avatar!.isEmpty) ? const Icon(Icons.person, size: 40, color: Colors.grey) : null,
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(user.name, style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Colors.white)),
                          const SizedBox(height: 4),
                          Text(user.email, style: const TextStyle(color: Colors.white70)),
                        ],
                      ),
                    )
                  ],
                ),
              ),

            const SizedBox(height: 20),

            // Wallet Card (Only if logged in)
            if (user != null)
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
                child: Card(
                  elevation: 2,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                  child: Padding(
                    padding: const EdgeInsets.all(20.0),
                    child: Row(
                      children: [
                        const Icon(Icons.account_balance_wallet, color: Color(0xFFD59E06), size: 40),
                        const SizedBox(width: 16),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('رصيد المحفظة', style: TextStyle(color: Colors.grey)),
                            const SizedBox(height: 4),
                            Text(
                              '${user.walletBalance} د.ع',
                              style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Color(0xFF6D0E16)),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ),
              ),

            // General App Settings (Always Visible)
            _buildSectionHeader('إعدادات التطبيق'),
            SwitchListTile(
              secondary: Icon(isDarkMode ? Icons.dark_mode : Icons.light_mode, color: const Color(0xFF6D0E16)),
              title: const Text('الوضع الليلي', style: TextStyle(fontWeight: FontWeight.w600)),
              value: isDarkMode,
              activeColor: const Color(0xFF6D0E16),
              onChanged: (v) {
                ref.read(isDarkModeProvider.notifier).toggle(v);
              },
            ),
            _buildProfileOption(Icons.notifications_active_outlined, 'إعدادات الإشعارات', () {}),
            _buildProfileOption(Icons.language_outlined, 'لغة التطبيق (العربية)', () {}),

            // Profile Features (Needs Authentication to use but we show them or hide them)
            if (user != null) ...[
              const Divider(height: 30),
              _buildSectionHeader('خدمات الحساب'),
              _buildProfileOption(Icons.shopping_bag_outlined, 'متابعة الطلبات', () {}),
              _buildProfileOption(Icons.favorite_border, 'قائمة الرغبات (المفضلة)', () {}),
              _buildProfileOption(Icons.location_on_outlined, 'عناوين الشحن المحفوظة', () {}),
            ],

            const Divider(height: 30),
            _buildSectionHeader('الدعم والسياسات'),
            _buildProfileOption(Icons.support_agent, 'الدعم الفني والشكاوى', () {}),
            _buildProfileOption(Icons.info_outline, 'عن التطبيق', () {}),
            
            if (user != null) ...[
              const SizedBox(height: 20),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16.0),
                child: SizedBox(
                  width: double.infinity,
                  child: OutlinedButton.icon(
                    style: OutlinedButton.styleFrom(
                      foregroundColor: Colors.red,
                      side: const BorderSide(color: Colors.red),
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                    icon: const Icon(Icons.logout),
                    label: const Text('تسجيل الخروج', style: TextStyle(fontWeight: FontWeight.bold)),
                    onPressed: () {
                      ref.read(authProvider.notifier).logout();
                    },
                  ),
                ),
              ),
            ],
            
            const SizedBox(height: 120), // Padding for liquid bottom navigation bar
          ],
        ),
      ),
    );
  }

  Widget _buildSectionHeader(String title) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20.0, vertical: 8.0),
      child: Align(
        alignment: Alignment.centerRight,
        child: Text(
          title, 
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.grey.shade600),
        ),
      ),
    );
  }

  Widget _buildProfileOption(IconData icon, String title, VoidCallback onTap, {Color? color}) {
    return ListTile(
      leading: Icon(icon, color: color ?? const Color(0xFF6D0E16)),
      title: Text(title, style: TextStyle(color: color, fontWeight: FontWeight.w600)),
      trailing: color == null ? const Icon(Icons.arrow_forward_ios, size: 16, color: Colors.grey) : null,
      onTap: onTap,
    );
  }
}
