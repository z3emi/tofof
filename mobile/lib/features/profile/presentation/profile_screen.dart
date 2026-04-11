import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:skeletonizer/skeletonizer.dart';
import 'package:flutter/services.dart';

import '../../auth/providers/auth_provider.dart';

class ProfileScreen extends ConsumerWidget {
  final bool showAppBar;

  const ProfileScreen({super.key, this.showAppBar = true});

  const ProfileScreen.embedded({super.key, this.showAppBar = false});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final authState = ref.watch(authProvider);
    final user = authState.user;

    if (authState.isLoading && user == null) {
      final skeletonBody = Skeletonizer(
        enabled: true,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(16, 20, 16, 120),
          children: const [
            Card(child: SizedBox(height: 180)),
            SizedBox(height: 14),
            Card(child: SizedBox(height: 88)),
            SizedBox(height: 12),
            ListTile(
              leading: Icon(Icons.shopping_bag_outlined),
              title: Text('طلباتي'),
            ),
            ListTile(
              leading: Icon(Icons.discount_outlined),
              title: Text('أكواد الخصم'),
            ),
            ListTile(
              leading: Icon(Icons.favorite_border),
              title: Text('المفضلة'),
            ),
            ListTile(
              leading: Icon(Icons.location_on_outlined),
              title: Text('عناويني'),
            ),
            ListTile(
              leading: Icon(Icons.settings_outlined),
              title: Text('إعدادات التطبيق'),
            ),
          ],
        ),
      );

      if (!showAppBar) {
        return skeletonBody;
      }

      return Scaffold(
        appBar: AppBar(title: const Text('حسابي')),
        body: skeletonBody,
      );
    }

    if (user == null) {
      final guestBody = ListView(
        padding: const EdgeInsets.fromLTRB(16, 20, 16, 120),
        children: [
          Container(
            padding: const EdgeInsets.all(22),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(24),
              gradient: const LinearGradient(
                colors: [
                  Color(0xFF6D0E16),
                  Color(0xFF9B1B24),
                  Color(0xFF571017),
                ],
                begin: Alignment.topRight,
                end: Alignment.bottomLeft,
              ),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF6D0E16).withValues(alpha: 0.18),
                  blurRadius: 24,
                  offset: const Offset(0, 12),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                const CircleAvatar(
                  radius: 38,
                  backgroundColor: Colors.white,
                  child: Icon(
                    Icons.person_outline_rounded,
                    size: 38,
                    color: Color(0xFF6D0E16),
                  ),
                ),
                const SizedBox(height: 14),
                const Text(
                  'مرحبًا بك في طفوف',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w800,
                    fontSize: 22,
                  ),
                ),
                const SizedBox(height: 8),
                const Text(
                  'سجل دخولك حتى تظهر طلباتك، أكواد الخصم، المفضلة، العناوين، والمحفظة.',
                  textAlign: TextAlign.center,
                  style: TextStyle(color: Colors.white70, height: 1.5),
                ),
                const SizedBox(height: 18),
                ElevatedButton(
                  onPressed: () => context.push('/login'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.white,
                    foregroundColor: const Color(0xFF6D0E16),
                  ),
                  child: const Text('تسجيل دخول'),
                ),
                const SizedBox(height: 10),
                OutlinedButton(
                  onPressed: () => context.push('/register'),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: Colors.white,
                    side: const BorderSide(color: Colors.white),
                  ),
                  child: const Text('إنشاء حساب جديد'),
                ),
                const SizedBox(height: 6),
                TextButton(
                  onPressed: () => context.push('/reset-password'),
                  style: TextButton.styleFrom(foregroundColor: Colors.white70),
                  child: const Text('نسيت كلمة المرور؟'),
                ),
              ],
            ),
          ),
          const SizedBox(height: 14),
          Card(
            child: ListTile(
              leading: const Icon(Icons.settings_outlined),
              title: const Text('إعدادات التطبيق'),
              subtitle: const Text('الإشعارات والوضع الليلي واللغة'),
              trailing: const Icon(Icons.arrow_forward_ios, size: 16),
              onTap: () => context.push('/settings'),
            ),
          ),
        ],
      );

      if (!showAppBar) {
        return guestBody;
      }

      return Scaffold(
        appBar: AppBar(title: const Text('حسابي')),
        body: guestBody,
      );
    }

    final userBody = SingleChildScrollView(
      padding: const EdgeInsets.only(bottom: 120),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Container(
            margin: const EdgeInsets.fromLTRB(16, 18, 16, 12),
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(26),
              gradient: const LinearGradient(
                colors: [
                  Color(0xFF6D0E16),
                  Color(0xFF8F1A24),
                  Color(0xFF531018),
                ],
                begin: Alignment.topRight,
                end: Alignment.bottomLeft,
              ),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF6D0E16).withValues(alpha: 0.18),
                  blurRadius: 28,
                  offset: const Offset(0, 14),
                ),
              ],
            ),
            child: Row(
              children: [
                _ProfileAvatar(avatarPath: user.avatar),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Expanded(
                            child: Text(
                              user.name,
                              style: const TextStyle(
                                fontSize: 22,
                                fontWeight: FontWeight.w800,
                                color: Colors.white,
                              ),
                            ),
                          ),
                          IconButton(
                            onPressed: () => context.push('/profile/edit'),
                            icon: const Icon(
                              Icons.edit_outlined,
                              color: Colors.white,
                            ),
                            tooltip: 'تعديل',
                          ),
                        ],
                      ),
                      const SizedBox(height: 4),
                      Text(
                        user.phoneNumber,
                        style: const TextStyle(
                          color: Colors.white70,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      if (user.email.isNotEmpty) ...[
                        const SizedBox(height: 2),
                        Text(
                          user.email,
                          style: const TextStyle(
                            color: Colors.white54,
                            fontSize: 12,
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Column(
              children: [
                _WalletHighlightCard(
                  balanceText: '${_formatAmount(user.walletBalance)} د.ع',
                ),
                const SizedBox(height: 12),
                _ReferralCodeCard(referralCode: user.referralCode),
              ],
            ),
          ),
          const SizedBox(height: 14),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Card(
              child: Column(
                children: [
                  _buildProfileOption(
                    Icons.shopping_bag_outlined,
                    'طلباتي',
                    () => context.push('/profile/orders'),
                  ),
                  _buildProfileOption(
                    Icons.discount_outlined,
                    'أكواد الخصم',
                    () => context.push('/profile/discounts'),
                  ),
                  _buildProfileOption(
                    Icons.favorite_border,
                    'المفضلة',
                    () => context.push('/profile/favorites'),
                  ),
                  _buildProfileOption(
                    Icons.location_on_outlined,
                    'عناويني',
                    () => context.push('/profile/addresses'),
                  ),
                  _buildProfileOption(
                    Icons.settings_outlined,
                    'إعدادات التطبيق',
                    () => context.push('/settings'),
                  ),
                  _buildProfileOption(
                    Icons.support_agent,
                    'الدعم الفني والشكاوى',
                    () {},
                  ),
                  _buildProfileOption(
                    Icons.logout,
                    'تسجيل الخروج',
                    () => ref.read(authProvider.notifier).logout(),
                    color: Colors.red,
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),
        ],
      ),
    );

    if (!showAppBar) {
      return userBody;
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('حسابي'),
        actions: [
          IconButton(
            icon: const Icon(Icons.settings_outlined),
            onPressed: () => context.push('/settings'),
          ),
        ],
      ),
      body: userBody,
    );
  }

  static String _formatAmount(double amount) {
    final normalized = amount.toStringAsFixed(2);
    final parts = normalized.split('.');
    final whole = parts[0];
    final withSeparators = whole.replaceAllMapped(
      RegExp(r'\B(?=(\d{3})+(?!\d))'),
      (m) => ',',
    );
    return '$withSeparators.${parts[1]}';
  }

  static Widget _buildProfileOption(
    IconData icon,
    String title,
    VoidCallback? onTap, {
    Color? color,
  }) {
    return ListTile(
      leading: Icon(icon, color: color ?? const Color(0xFF6D0E16)),
      title: Text(
        title,
        style: TextStyle(color: color, fontWeight: FontWeight.w600),
      ),
      trailing: color == null
          ? const Icon(Icons.arrow_forward_ios, size: 16, color: Colors.grey)
          : null,
      onTap: onTap,
    );
  }
}

class _ProfileAvatar extends StatelessWidget {
  final String? avatarPath;

  const _ProfileAvatar({required this.avatarPath});

  @override
  Widget build(BuildContext context) {
    final value = avatarPath?.trim();
    final isDefault =
        value == null ||
        value.isEmpty ||
        value.toLowerCase().contains('default.png') ||
        value.toLowerCase().contains('default.jpg');

    return CircleAvatar(
      radius: 38,
      backgroundColor: Colors.white,
      child: isDefault
          ? const Icon(Icons.person, size: 40, color: Colors.grey)
          : ClipOval(
              child: Image.network(
                value.startsWith('http')
                    ? value
                    : 'https://www.tofofstore.com/storage/$value',
                width: 76,
                height: 76,
                fit: BoxFit.cover,
                errorBuilder: (_, __, ___) =>
                    const Icon(Icons.person, size: 40, color: Colors.grey),
              ),
            ),
    );
  }
}

class _WalletHighlightCard extends StatelessWidget {
  final String balanceText;

  const _WalletHighlightCard({required this.balanceText});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(20),
        gradient: const LinearGradient(
          colors: [Color(0xFF0E6D58), Color(0xFF139A78), Color(0xFF0F7D62)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0E6D58).withValues(alpha: 0.18),
            blurRadius: 18,
            offset: const Offset(0, 10),
          ),
        ],
      ),
      child: Row(
        children: [
          Container(
            width: 46,
            height: 46,
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.22),
              borderRadius: BorderRadius.circular(14),
            ),
            child: const Icon(
              Icons.account_balance_wallet_outlined,
              color: Colors.white,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'رصيد المحفظة',
                  style: TextStyle(
                    color: Colors.white70,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  balanceText,
                  style: const TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w800,
                    fontSize: 22,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _ReferralCodeCard extends StatelessWidget {
  final String? referralCode;

  const _ReferralCodeCard({required this.referralCode});

  @override
  Widget build(BuildContext context) {
    final code = referralCode?.trim();
    final hasCode = code != null && code.isNotEmpty;

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFFFDF5E8),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE4CF9B)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Row(
            children: [
              Icon(Icons.card_giftcard_outlined, color: Color(0xFF8B5E00)),
              SizedBox(width: 8),
              Text(
                'كود الدعوة',
                style: TextStyle(
                  fontWeight: FontWeight.w800,
                  color: Color(0xFF6B4700),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Row(
            children: [
              Expanded(
                child: Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 10,
                  ),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFFE4CF9B)),
                  ),
                  child: Text(
                    hasCode ? code : 'غير متوفر',
                    style: TextStyle(
                      fontWeight: FontWeight.w800,
                      letterSpacing: hasCode ? 1.0 : 0,
                      color: hasCode
                          ? const Color(0xFF2F2F2F)
                          : Colors.grey.shade600,
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 8),
              FilledButton.icon(
                onPressed: hasCode
                    ? () async {
                        await Clipboard.setData(ClipboardData(text: code));
                        if (!context.mounted) return;
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('تم نسخ كود الدعوة')),
                        );
                      }
                    : null,
                icon: const Icon(Icons.copy_outlined, size: 18),
                label: const Text('نسخ'),
                style: FilledButton.styleFrom(
                  backgroundColor: const Color(0xFF8B5E00),
                  foregroundColor: Colors.white,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
