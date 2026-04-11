import 'dart:ui';

import 'package:flutter/material.dart';
import '../../home/presentation/home_view.dart';
import '../../categories/presentation/categories_screen.dart';

import '../../cart/presentation/cart_screen.dart';
import '../../profile/presentation/profile_screen.dart';

class MainScreen extends StatefulWidget {
  const MainScreen({super.key});

  @override
  State<MainScreen> createState() => _MainScreenState();
}

class _MainScreenState extends State<MainScreen> {
  int _currentIndex = 0;
  static const double _headerHeight = 72;

  final List<Widget> _pages = [
    const HomeView.embedded(),
    const CategoriesScreen.embedded(),
    const CartScreen.embedded(),
    const ProfileScreen.embedded(),
  ];

  bool get _showHeader => _currentIndex != 3;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Scaffold(
      extendBody: true,
      body: Stack(
        children: [
          Positioned.fill(
            child: AnimatedPadding(
              duration: const Duration(milliseconds: 220),
              curve: Curves.easeOut,
              padding: EdgeInsets.only(
                top: _showHeader ? _headerHeight : 12,
                bottom: 98,
              ),
              child: IndexedStack(
                index: _currentIndex,
                children: _pages,
              ),
            ),
          ),
          Positioned(
            top: 0,
            right: 0,
            left: 0,
            child: AnimatedSlide(
              duration: const Duration(milliseconds: 220),
              offset: _showHeader ? Offset.zero : const Offset(0, -1),
              curve: Curves.easeOut,
              child: IgnorePointer(
                ignoring: !_showHeader,
                child: Container(
                  height: _headerHeight,
                  decoration: BoxDecoration(
                    color: theme.scaffoldBackgroundColor.withValues(alpha: 0.92),
                    border: Border(bottom: BorderSide(color: theme.dividerColor.withValues(alpha: 0.2))),
                  ),
                  child: SafeArea(
                    bottom: false,
                    child: Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      child: Row(
                        children: [
                          Image.asset('assets/images/logo.png', height: 32),
                          const Spacer(),
                          IconButton(onPressed: () {}, icon: const Icon(Icons.search)),
                          IconButton(onPressed: () {}, icon: const Icon(Icons.notifications_none)),
                        ],
                      ),
                    ),
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
      bottomNavigationBar: Container(
        margin: const EdgeInsets.fromLTRB(12, 0, 12, 12),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(22),
          child: BackdropFilter(
            filter: ImageFilter.blur(sigmaX: 14, sigmaY: 14),
            child: Container(
              height: 76,
              decoration: BoxDecoration(
                color: theme.colorScheme.surface.withValues(alpha: 0.75),
                borderRadius: BorderRadius.circular(22),
                border: Border.all(color: Colors.white.withValues(alpha: 0.28)),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.1),
                    blurRadius: 20,
                    offset: const Offset(0, 10),
                  ),
                ],
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceAround,
                children: [
                  _NavItem(
                    label: 'الرئيسية',
                    icon: Icons.home_outlined,
                    activeIcon: Icons.home,
                    active: _currentIndex == 0,
                    onTap: () => setState(() => _currentIndex = 0),
                  ),
                  _NavItem(
                    label: 'الأقسام',
                    icon: Icons.grid_view_outlined,
                    activeIcon: Icons.grid_view,
                    active: _currentIndex == 1,
                    onTap: () => setState(() => _currentIndex = 1),
                  ),
                  _NavItem(
                    label: 'السلة',
                    icon: Icons.shopping_cart_outlined,
                    activeIcon: Icons.shopping_cart,
                    active: _currentIndex == 2,
                    onTap: () => setState(() => _currentIndex = 2),
                  ),
                  _NavItem(
                    label: 'حسابي',
                    icon: Icons.person_outline,
                    activeIcon: Icons.person,
                    active: _currentIndex == 3,
                    onTap: () => setState(() => _currentIndex = 3),
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

class _NavItem extends StatelessWidget {
  final String label;
  final IconData icon;
  final IconData activeIcon;
  final bool active;
  final VoidCallback onTap;

  const _NavItem({
    required this.label,
    required this.icon,
    required this.activeIcon,
    required this.active,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    const activeColor = Color(0xFF6D0E16);

    return Expanded(
      child: InkWell(
        onTap: onTap,
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 220),
          curve: Curves.easeOut,
          margin: const EdgeInsets.symmetric(vertical: 10, horizontal: 4),
          decoration: BoxDecoration(
            color: active ? activeColor.withValues(alpha: 0.12) : Colors.transparent,
            borderRadius: BorderRadius.circular(16),
          ),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(active ? activeIcon : icon, color: active ? activeColor : Colors.grey[600], size: 22),
              const SizedBox(height: 3),
              Text(
                label,
                style: TextStyle(
                  color: active ? activeColor : Colors.grey[600],
                  fontWeight: active ? FontWeight.w700 : FontWeight.w500,
                  fontSize: 12,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
