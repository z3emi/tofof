import 'dart:ui';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:google_fonts/google_fonts.dart';

import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_dimensions.dart';
import '../../../core/widgets/app_snackbar.dart';
import '../../cart/presentation/cart_screen.dart';
import '../../cart/providers/cart_provider.dart';
import '../../categories/presentation/categories_screen.dart';
import '../../home/presentation/home_view.dart';
import '../../store/presentation/store_screen.dart';
import '../../profile/presentation/profile_screen.dart';

class MainScreen extends ConsumerStatefulWidget {
  const MainScreen({super.key});

  @override
  ConsumerState<MainScreen> createState() => _MainScreenState();
}

class _MainScreenState extends ConsumerState<MainScreen> {
  int _currentIndex = 0;

  final List<Widget> _pages = const [
    HomeView.embedded(),
    StoreScreen.embedded(),
    CartScreen.embedded(),
    CategoriesScreen.embedded(),
    ProfileScreen.embedded(),
  ];

  bool get _showHeader => _currentIndex != 4;

  @override
  Widget build(BuildContext context) {
    final cartCount = ref.watch(cartProvider.select((state) => state.count));
    final isArabic = Localizations.localeOf(context).languageCode == 'ar';
    final searchText = isArabic ? 'البحث' : 'Search';
    final topInset = MediaQuery.paddingOf(context).top;
    final contentTopPadding = _showHeader
        ? topInset + AppDimensions.appBarHeight
        : topInset;

    return Scaffold(
      backgroundColor: AppColors.background,
      body: Stack(
        children: [
          // ── Page content ────────────────────────────────────────────────
          Positioned.fill(
            child: AnimatedPadding(
              duration: const Duration(milliseconds: 220),
              curve: Curves.easeOut,
              // التعديل الأول: تم تغيير bottom إلى 0 لكي يمتد المحتوى خلف القائمة
              padding: EdgeInsets.only(top: contentTopPadding, bottom: 0),
              child: IndexedStack(index: _currentIndex, children: _pages),
            ),
          ),

          // ── Top header bar ──────────────────────────────────────────────
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
                  decoration: BoxDecoration(
                    color: AppColors.background.withValues(alpha: 0.8),
                    border: Border(bottom: BorderSide(color: AppColors.border)),
                  ),
                  child: ClipRRect(
                    child: BackdropFilter(
                      filter: ImageFilter.blur(sigmaX: 16, sigmaY: 16),
                      child: SafeArea(
                        bottom: false,
                        child: Padding(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 24,
                            vertical: 12,
                          ),
                          child: SizedBox(
                            height: 48,
                            child: Row(
                              textDirection: TextDirection.ltr,
                              crossAxisAlignment: CrossAxisAlignment.center,
                              children: [
                                _HeaderIconButton(
                                  icon: Icons.search,
                                  onTap: () =>
                                      _showComingSoon(context, searchText),
                                ),
                                const Spacer(),
                                Image.asset(
                                  'assets/images/logo.png', // التأكد من وجود اللوجو
                                  height: 40,
                                  fit: BoxFit.contain,
                                  errorBuilder: (context, error, stackTrace) =>
                                      Text(
                                        'TOFOF',
                                        style: GoogleFonts.notoSerif(
                                          color: AppColors.primary,
                                          fontSize: 22,
                                          fontWeight: FontWeight.bold,
                                          letterSpacing: 2.0,
                                        ),
                                      ),
                                ),
                                const Spacer(),
                                _HeaderCartButton(
                                  count: cartCount,
                                  onTap: () =>
                                      setState(() => _currentIndex = 2),
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
            ),
          ),

          // ── Floating Liquid Glass bottom nav ──────────────────────────────
          Positioned(
            bottom: MediaQuery.paddingOf(context).bottom + 20,
            left: 20,
            right: 20,
            child: _LiquidGlassNavBar(
              currentIndex: _currentIndex,
              cartCount: cartCount,
              isArabic: isArabic,
              onTap: (i) => setState(() => _currentIndex = i),
            ),
          ),
        ],
      ),
    );
  }

  void _showComingSoon(BuildContext context, String title) {
    final isArabic = Localizations.localeOf(context).languageCode == 'ar';
    showTimedSnackBar(
      context,
      isArabic ? '$title قريباً' : '$title coming soon',
      backgroundColor: AppColors.primary,
    );
  }
}

// ─── Floating Dark Liquid Glass Nav Bar ─────────────────────────────────────

class _LiquidGlassNavBar extends StatelessWidget {
  final int currentIndex;
  final int cartCount;
  final bool isArabic;
  final ValueChanged<int> onTap;

  const _LiquidGlassNavBar({
    required this.currentIndex,
    required this.cartCount,
    required this.isArabic,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    const borderRadius = BorderRadius.all(Radius.circular(38));

    final items = [
      (
        label: isArabic ? 'الرئيسية' : 'Home',
        icon: Icons.home_outlined,
        activeIcon: Icons.home,
      ),
      (
        label: isArabic ? 'المتجر' : 'Store',
        icon: Icons.shopping_bag_outlined,
        activeIcon: Icons.shopping_bag,
      ),
      (
        label: isArabic ? 'السلة' : 'Cart',
        icon: Icons.shopping_cart_outlined,
        activeIcon: Icons.shopping_cart,
      ),
      (
        label: isArabic ? 'الأقسام' : 'Categories',
        icon: Icons.grid_view_outlined,
        activeIcon: Icons.grid_view,
      ),
      (
        label: isArabic ? 'حسابي' : 'Profile',
        icon: Icons.person_outline,
        activeIcon: Icons.person,
      ),
    ];

    return Container(
      decoration: BoxDecoration(
        borderRadius: borderRadius,
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF6D0E16).withValues(alpha: 0.14),
            blurRadius: 36,
            offset: const Offset(0, 14),
          ),
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.08),
            blurRadius: 16,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: borderRadius,
        child: BackdropFilter(
          filter: ImageFilter.blur(sigmaX: 28, sigmaY: 28),
          child: Container(
            height: 74,
            decoration: BoxDecoration(
              borderRadius: borderRadius,
              gradient: LinearGradient(
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
                colors: [
                  Colors.white.withValues(alpha: 0.9),
                  Colors.white.withValues(alpha: 0.68),
                ],
              ),
              border: Border.all(
                color: Colors.white.withValues(alpha: 0.28),
                width: 1.2,
              ),
            ),
            child: Stack(
              children: [
                Padding(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 6,
                  ),
                  child: Row(
                    children: [
                      for (int i = 0; i < items.length; i++)
                        Expanded(
                          child: _NavItem(
                            label: items[i].label,
                            icon: items[i].icon,
                            activeIcon: items[i].activeIcon,
                            active: currentIndex == i,
                            isCenter: i == 2,
                            badgeCount: i == 2 ? cartCount : 0,
                            onTap: () => onTap(i),
                          ),
                        ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _HeaderIconButton extends StatelessWidget {
  final IconData icon;
  final VoidCallback onTap;

  const _HeaderIconButton({required this.icon, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(999),
        child: Container(
          width: 44,
          height: 44,
          alignment: Alignment.center,
          child: Icon(icon, color: AppColors.primary, size: 26),
        ),
      ),
    );
  }
}

class _HeaderCartButton extends StatelessWidget {
  final int count;
  final VoidCallback onTap;

  const _HeaderCartButton({required this.count, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(999),
        child: SizedBox(
          width: 44,
          height: 44,
          child: Stack(
            clipBehavior: Clip.none,
            children: [
              const Center(
                child: Icon(
                  Icons.shopping_cart_outlined,
                  color: Color(0xFF6D0E16),
                  size: 26,
                ),
              ),
              if (count > 0)
                Positioned(
                  top: 3,
                  right: 3,
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 5,
                      vertical: 1,
                    ),
                    decoration: BoxDecoration(
                      color: Colors.red,
                      borderRadius: BorderRadius.circular(999),
                    ),
                    child: Text(
                      count > 99 ? '99+' : '$count',
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 9,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                  ),
                ),
            ],
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
  final bool isCenter;
  final VoidCallback onTap;
  final int badgeCount;

  const _NavItem({
    required this.label,
    required this.icon,
    required this.activeIcon,
    required this.active,
    this.isCenter = false,
    required this.onTap,
    this.badgeCount = 0,
  });

  @override
  Widget build(BuildContext context) {
    final Color iconColor = active ? AppColors.primary : Colors.grey.shade600;
    final iconToShow = active ? activeIcon : icon;

    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      splashColor: Colors.transparent,
      highlightColor: Colors.transparent,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 240),
        height: double.infinity,
        margin: const EdgeInsets.symmetric(horizontal: 2),
        padding: const EdgeInsets.symmetric(vertical: 1),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Stack(
              clipBehavior: Clip.none,
              children: [
                if (isCenter)
                  AnimatedContainer(
                    duration: const Duration(milliseconds: 240),
                    width: 40,
                    height: 40,
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(14),
                      gradient: active
                          ? LinearGradient(
                              colors: [
                                AppColors.primary.withValues(alpha: 0.22),
                                AppColors.primary.withValues(alpha: 0.08),
                              ],
                              begin: Alignment.topLeft,
                              end: Alignment.bottomRight,
                            )
                          : null,
                      color: active ? null : Colors.transparent,
                      border: Border.all(
                        color: active
                            ? AppColors.primary.withValues(alpha: 0.55)
                            : Colors.grey.shade300,
                        width: 1,
                      ),
                    ),
                    child: Icon(
                      iconToShow,
                      color: active ? AppColors.primary : Colors.grey.shade700,
                      size: 20,
                    ),
                  )
                else
                  AnimatedContainer(
                    duration: const Duration(milliseconds: 240),
                    padding: const EdgeInsets.all(6),
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(12),
                      color: active
                          ? AppColors.primary.withValues(alpha: 0.12)
                          : Colors.transparent,
                    ),
                    child: Icon(iconToShow, color: iconColor, size: 22),
                  ),

                if (badgeCount > 0)
                  Positioned(
                    top: -4,
                    right: -4,
                    child: Container(
                      width: 16,
                      height: 16,
                      alignment: Alignment.center,
                      decoration: const BoxDecoration(
                        color: Colors.red,
                        shape: BoxShape.circle,
                      ),
                      child: Text(
                        badgeCount > 9 ? '9+' : '$badgeCount',
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 8,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                    ),
                  ),
              ],
            ),
            const SizedBox(height: 2),
            Text(
              label,
              style: GoogleFonts.manrope(
                color: iconColor,
                fontWeight: active ? FontWeight.bold : FontWeight.w600,
                fontSize: 9,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
