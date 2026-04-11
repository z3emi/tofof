import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:skeletonizer/skeletonizer.dart';

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
          padding: const EdgeInsets.fromLTRB(16, 16, 16, 120),
          children: const [
            ListTile(
              leading: CircleAvatar(radius: 25),
              title: Text('اسم المستخدم'),
              subtitle: Text('email@example.com'),
            ),
            SizedBox(height: 12),
            Card(child: SizedBox(height: 80)),
            SizedBox(height: 12),
            ListTile(leading: Icon(Icons.shopping_bag_outlined), title: Text('طلباتي')),
            ListTile(leading: Icon(Icons.favorite_border), title: Text('المفضلة')),
            ListTile(leading: Icon(Icons.settings_outlined), title: Text('الإعدادات')),
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
        padding: const EdgeInsets.fromLTRB(16, 24, 16, 120),
        children: [
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(20),
              gradient: const LinearGradient(
                colors: [Color(0xFF6D0E16), Color(0xFF901B24)],
                begin: Alignment.topRight,
                end: Alignment.bottomLeft,
              ),
            ),
            child: Column(
              children: [
                const CircleAvatar(radius: 34, backgroundColor: Colors.white, child: Icon(Icons.person, size: 34, color: Color(0xFF6D0E16))),
                const SizedBox(height: 12),
                const Text('مرحبًا بك في طفوف', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 20)),
                const SizedBox(height: 8),
                const Text('سجّل دخولك للوصول إلى الطلبات والمفضلة والعناوين.', textAlign: TextAlign.center, style: TextStyle(color: Colors.white70)),
                const SizedBox(height: 16),
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton(
                        onPressed: () => context.push('/register'),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: Colors.white,
                          side: const BorderSide(color: Colors.white),
                        ),
                        child: const Text('تسجيل'),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: ElevatedButton(
                        onPressed: () => context.push('/login'),
                        style: ElevatedButton.styleFrom(backgroundColor: Colors.white, foregroundColor: const Color(0xFF6D0E16)),
                        child: const Text('تسجيل دخول'),
                      ),
                    ),
                  ],
                )
              ],
            ),
          ),
          const SizedBox(height: 18),
          _buildProfileOption(Icons.shopping_bag_outlined, 'طلباتي', null),
          _buildProfileOption(Icons.favorite_border, 'المفضلة', null),
          _buildProfileOption(Icons.location_on_outlined, 'عناويني', null),
          _buildProfileOption(Icons.settings_outlined, 'إعدادات التطبيق', () => context.push('/settings')),
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
        children: [
          Container(
            margin: const EdgeInsets.all(16),
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(18),
              gradient: const LinearGradient(
                colors: [Color(0xFF6D0E16), Color(0xFF8F1A24)],
                begin: Alignment.topRight,
                end: Alignment.bottomLeft,
              ),
            ),
            child: Row(
              children: [
                CircleAvatar(
                  radius: 35,
                  backgroundColor: Colors.white,
                  backgroundImage: user.avatar != null ? NetworkImage(user.avatar!) : null,
                  child: user.avatar == null ? const Icon(Icons.person, size: 40, color: Colors.grey) : null,
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(user.name, style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Colors.white)),
                      const SizedBox(height: 4),
                      Text(user.phoneNumber, style: const TextStyle(color: Colors.white70)),
                      if (user.email.isNotEmpty) ...[
                        const SizedBox(height: 2),
                        Text(user.email, style: const TextStyle(color: Colors.white54, fontSize: 12)),
                      ],
                    ],
                  ),
                )
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16.0),
            child: Card(
              elevation: 3,
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
          const SizedBox(height: 8),
          _buildProfileOption(Icons.shopping_bag_outlined, 'متابعة الطلبات', () {}),
          _buildProfileOption(Icons.favorite_border, 'قائمة الرغبات (المفضلة)', () {}),
          _buildProfileOption(Icons.location_on_outlined, 'عناوين الشحن المحفوظة', () {}),
          _buildProfileOption(Icons.settings_outlined, 'إعدادات التطبيق', () => context.push('/settings')),
          const Divider(),
          _buildProfileOption(Icons.support_agent, 'الدعم الفني والشكاوى', () {}),
          _buildProfileOption(
            Icons.logout,
            'تسجيل الخروج',
            () => ref.read(authProvider.notifier).logout(),
            color: Colors.red,
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

  static Widget _buildProfileOption(IconData icon, String title, VoidCallback? onTap, {Color? color}) {
    return ListTile(
      leading: Icon(icon, color: color ?? const Color(0xFF6D0E16)),
      title: Text(title, style: TextStyle(color: color, fontWeight: FontWeight.w600)),
      trailing: color == null ? const Icon(Icons.arrow_forward_ios, size: 16, color: Colors.grey) : null,
      onTap: onTap,
    );
  }
}
