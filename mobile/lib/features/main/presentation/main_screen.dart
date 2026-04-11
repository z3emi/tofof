import 'dart:ui';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:google_fonts/google_fonts.dart';

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
    CategoriesScreen.embedded(),
    CartScreen.embedded(),
    ProfileScreen.embedded(),
  ];

  bool get _showHeader => _currentIndex != 4;

  @override
  Widget build(BuildContext context) {
    final cartCount = ref.watch(cartProvider.select((state) => state.count));

    return Scaffold(
      // ✦ extendBody removed — nav bar is now inside the Stack
      //   so BackdropFilter blurs body content on Android correctly.
      body: Stack(
        children: [
          // ── Page content ────────────────────────────────────────────────
          Positioned.fill(
            child: AnimatedPadding(
              duration: const Duration(milliseconds: 220),
              curve: Curves.easeOut,
              padding: EdgeInsets.only(top: _showHeader ? 78 : 0, bottom: 104),
              child: IndexedStack(index: _currentIndex, children: _pages),
            ),
          ),

          // ── Top header bar ───────────────────────────────────────────────
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
                    color: Colors.white.withValues(alpha: 0.8),
                    border: Border(
                      bottom: BorderSide(color: const Color(0xFFEEEEEE)),
                    ),
                  ),
                  child: ClipRRect(
                    child: BackdropFilter(
                      filter: ImageFilter.blur(sigmaX: 12, sigmaY: 12),
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
                                      _showComingSoon(context, 'البحث'),
                                ),
                                const Spacer(),
                                Image.asset(
                                  'assets/images/logo.png',
                                  height: 40,
                                  fit: BoxFit.contain,
                                ),
                                const Spacer(),
                                _HeaderIconButton(
                                  icon: Icons.notifications_none,
                                  onTap: () =>
                                      _showComingSoon(context, 'الإشعارات'),
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

          // ── Liquid Glass bottom nav — inside Stack for Android blur ──────
          Positioned(
            bottom: 0,
            left: 0,
            right: 0,
            child: SafeArea(
              top: false,
              child: Padding(
                padding: const EdgeInsets.fromLTRB(18, 0, 18, 16),
                child: _LiquidGlassNavBar(
                  currentIndex: _currentIndex,
                  cartCount: cartCount,
                  onTap: (i) => setState(() => _currentIndex = i),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  void _showComingSoon(BuildContext context, String title) {
    ScaffoldMessenger.of(
      context,
    ).showSnackBar(SnackBar(content: Text('$title قريباً')));
  }
}

// ─── Liquid Glass Nav Bar ────────────────────────────────────────────────────

class _LiquidGlassNavBar extends StatelessWidget {
  final int currentIndex;
  final int cartCount;
  final ValueChanged<int> onTap;

  const _LiquidGlassNavBar({
    required this.currentIndex,
    required this.cartCount,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    const radius = Radius.circular(999.0);
    const borderRadius = BorderRadius.all(radius);

    final items = [
      (label: 'الرئيسية', icon: Icons.home_outlined, activeIcon: Icons.home),
      (label: 'المتجر', icon: Icons.shopping_bag_outlined, activeIcon: Icons.shopping_bag),
      (label: 'السلة', icon: Icons.shopping_cart_outlined, activeIcon: Icons.shopping_cart),
      (label: 'الأقسام', icon: Icons.grid_view_outlined, activeIcon: Icons.grid_view),
      (label: 'حسابي', icon: Icons.person_outline, activeIcon: Icons.person),
    ];

    return CustomPaint(
      painter: _LiquidGlassBorderPainter(),
      child: ClipRRect(
        borderRadius: borderRadius,
        child: BackdropFilter(
          filter: ImageFilter.blur(sigmaX: 32, sigmaY: 32),
          child: Container(
            decoration: const BoxDecoration(
              // ✦ No background colour – pure glass transparency
              color: Colors.transparent,
              borderRadius: borderRadius,
            ),
            child: Stack(
              children: [
                // ── Specular top-edge highlight (light hitting the top rim) ──
                Positioned(
                  top: 0,
                  left: 20,
                  right: 20,
                  child: Container(
                    height: 1,
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        colors: [
                          Colors.transparent,
                          Colors.white.withValues(alpha: 0.70),
                          Colors.white.withValues(alpha: 0.90),
                          Colors.white.withValues(alpha: 0.70),
                          Colors.transparent,
                        ],
                        stops: const [0.0, 0.25, 0.5, 0.75, 1.0],
                      ),
                    ),
                  ),
                ),
                // ── Inner frosted tint (very subtle) ────────────────────────
                Container(
                  decoration: BoxDecoration(
                    borderRadius: borderRadius,
                    gradient: LinearGradient(
                      begin: Alignment.topCenter,
                      end: Alignment.bottomCenter,
                      colors: [
                        Colors.white.withValues(alpha: 0.08),
                        Colors.white.withValues(alpha: 0.02),
                      ],
                    ),
                  ),
                ),
                // ── Nav items row ────────────────────────────────────────────
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceAround,
                    children: [
                      for (int i = 0; i < items.length; i++)
                        _NavItem(
                          label: items[i].label,
                          icon: items[i].icon,
                          activeIcon: items[i].activeIcon,
                          active: currentIndex == i,
                          badgeCount: i == 2 ? cartCount : 0,
                          onTap: () => onTap(i),
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

/// Paints the gradient glass border (light refraction simulation).
class _LiquidGlassBorderPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    const r = 999.0;
    final rect = Rect.fromLTWH(0, 0, size.width, size.height);
    final rrect = RRect.fromRectAndRadius(rect, const Radius.circular(r));

    // Shadow beneath the glass pill
    final shadowPaint = Paint()
      ..color = const Color(0xFF1A0A0D).withValues(alpha: 0.14)
      ..maskFilter = const MaskFilter.blur(BlurStyle.normal, 20);
    canvas.drawRRect(
      rrect.deflate(-4),
      shadowPaint,
    );

    // Gradient border: white highlight at top, subtle at bottom
    final borderPaint = Paint()
      ..style = PaintingStyle.stroke
      ..strokeWidth = 1.5
      ..shader = LinearGradient(
        begin: Alignment.topCenter,
        end: Alignment.bottomCenter,
        colors: [
          Colors.white.withValues(alpha: 0.60),
          Colors.white.withValues(alpha: 0.18),
          Colors.white.withValues(alpha: 0.04),
        ],
        stops: const [0.0, 0.5, 1.0],
      ).createShader(rect);

    canvas.drawRRect(rrect, borderPaint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

// ─── Header Icon Button ───────────────────────────────────────────────────────

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
          child: Icon(icon, color: const Color(0xFF6D0E16), size: 28),
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
  final int badgeCount;

  const _NavItem({
    required this.label,
    required this.icon,
    required this.activeIcon,
    required this.active,
    required this.onTap,
    this.badgeCount = 0,
  });

  @override
  Widget build(BuildContext context) {
    const activeColor = Color(0xFF6D0E16);
    final Color iconColor = active
        ? activeColor
        : Colors.black.withValues(alpha: 0.6);

    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(999),
      customBorder: const CircleBorder(),
      splashColor: Colors.transparent,
      highlightColor: Colors.transparent,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        constraints: const BoxConstraints(minWidth: 56),
        padding: const EdgeInsets.symmetric(vertical: 4),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Stack(
              clipBehavior: Clip.none,
              children: [
                Icon(active ? activeIcon : icon, color: iconColor, size: 24),
                if (active)
                  Positioned.fill(
                    child: Center(
                      child: Container(
                        width: 34,
                        height: 34,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: const Color(0xFFD59E06).withValues(alpha: 0.12),
                        ),
                      ),
                    ),
                  ),
                if (badgeCount > 0 && label == 'السلة')
                  Positioned(
                    top: -4,
                    right: -4,
                    child: Container(
                      width: 16,
                      height: 16,
                      alignment: Alignment.center,
                      decoration: const BoxDecoration(
                        color: Color(0xFFD59E06),
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
                fontWeight: active ? FontWeight.w800 : FontWeight.w700,
                fontSize: 10,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
