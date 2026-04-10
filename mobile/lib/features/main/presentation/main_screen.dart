import 'dart:ui';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../cart/providers/cart_provider.dart';

class MainScreen extends ConsumerStatefulWidget {
  final Widget child;
  const MainScreen({super.key, required this.child});

  @override
  ConsumerState<MainScreen> createState() => _MainScreenState();
}

class _MainScreenState extends ConsumerState<MainScreen> {
  int _calculateSelectedIndex(BuildContext context) {
    final location = GoRouterState.of(context).uri.toString();
    if (location.startsWith('/categories')) return 1;
    if (location.startsWith('/cart')) return 2;
    if (location.startsWith('/profile') || location.startsWith('/login')) return 3;
    return 0; // Home defaults to 0
  }

  void _onItemTapped(int index, BuildContext context) {
    switch (index) {
      case 0:
        context.go('/');
        break;
      case 1:
        context.go('/categories');
        break;
      case 2:
        context.go('/cart');
        break;
      case 3:
        context.go('/profile');
        break;
    }
  }

  PreferredSizeWidget? _buildAppBar(BuildContext context) {
    final location = GoRouterState.of(context).uri.toString();

    // Hide AppBar completely on Profile/Settings
    if (location.startsWith('/profile') || location.startsWith('/login') || location.startsWith('/register')) {
      return null;
    }

    // Hide AppBar on Product Details, it uses its own SliverAppBar
    if (location.startsWith('/product/') || location.startsWith('/category/')) {
      return null;
    }

    Widget title = Image.asset('assets/images/logo.png', height: 35);
    if (location.startsWith('/categories')) {
      title = const Text('الأقسام', style: TextStyle(color: Color(0xFF6D0E16), fontWeight: FontWeight.bold));
    } else if (location.startsWith('/cart')) {
      final cartCount = ref.watch(cartProvider).count;
      title = Text('السلة ($cartCount)', style: const TextStyle(color: Color(0xFF6D0E16), fontWeight: FontWeight.bold));
    }

    return AppBar(
      backgroundColor: Colors.white,
      surfaceTintColor: Colors.transparent,
      elevation: 0,
      centerTitle: true,
      title: title,
      actions: [
        IconButton(onPressed: () {}, icon: const Icon(Icons.search, color: Colors.black54)),
        IconButton(onPressed: () {}, icon: const Icon(Icons.notifications_none, color: Colors.black54)),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    final selectedIndex = _calculateSelectedIndex(context);
    final location = GoRouterState.of(context).uri.toString();
    final hideBottomNav = location.startsWith('/login') || location.startsWith('/register') || location.startsWith('/product/') || location.startsWith('/category/');

    return Scaffold(
      extendBody: true, // Crucial for floating/glass menu
      appBar: _buildAppBar(context),
      body: widget.child,
      bottomNavigationBar: hideBottomNav ? null : SafeArea(
        child: Container(
          margin: const EdgeInsets.only(left: 20, right: 20, bottom: 20),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(30),
            color: Colors.white.withValues(alpha: 0.8), // Glassmorphism base
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.1),
                blurRadius: 20,
                offset: const Offset(0, 5),
              )
            ],
          ),
          child: ClipRRect(
            borderRadius: BorderRadius.circular(30),
            child: BackdropFilter(
              filter: ImageFilter.blur(sigmaX: 10, sigmaY: 10),
              child: Theme(
                data: ThemeData(
                  splashColor: Colors.transparent,
                  highlightColor: Colors.transparent,
                ),
                child: BottomNavigationBar(
                  backgroundColor: Colors.transparent,
                  elevation: 0,
                  currentIndex: selectedIndex,
                  onTap: (index) => _onItemTapped(index, context),
                  type: BottomNavigationBarType.fixed,
                  selectedItemColor: const Color(0xFF6D0E16),
                  unselectedItemColor: Colors.grey.shade600,
                  showSelectedLabels: true,
                  showUnselectedLabels: false,
                  items: const [
                    BottomNavigationBarItem(icon: Icon(Icons.home_outlined), activeIcon: Icon(Icons.home), label: 'الرئيسية'),
                    BottomNavigationBarItem(icon: Icon(Icons.grid_view_outlined), activeIcon: Icon(Icons.grid_view), label: 'الأقسام'),
                    BottomNavigationBarItem(icon: Icon(Icons.shopping_cart_outlined), activeIcon: Icon(Icons.shopping_cart), label: 'السلة'),
                    BottomNavigationBarItem(icon: Icon(Icons.person_outline), activeIcon: Icon(Icons.person), label: 'حسابي'),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
