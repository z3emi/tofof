import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import 'package:skeletonizer/skeletonizer.dart';

import '../../../core/theme/app_dimensions.dart';
import '../providers/cart_provider.dart';

class CartScreen extends ConsumerStatefulWidget {
  final bool showAppBar;

  const CartScreen({super.key, this.showAppBar = true});

  const CartScreen.embedded({super.key, this.showAppBar = false});

  @override
  ConsumerState<CartScreen> createState() => _CartScreenState();
}

class _CartScreenState extends ConsumerState<CartScreen> {
  final _couponCtrl = TextEditingController();
  final Set<String> _removingKeys = <String>{};

  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(cartProvider.notifier).fetchCart());
  }

  @override
  void dispose() {
    _couponCtrl.dispose();
    super.dispose();
  }

  Future<void> _removeItemWithAnimation(String key) async {
    if (_removingKeys.contains(key)) return;

    setState(() => _removingKeys.add(key));

    // Keep animation short and cheap so it stays smooth on low-end devices.
    await Future.delayed(const Duration(milliseconds: 180));

    if (!mounted) return;
    await ref.read(cartProvider.notifier).removeItem(key);

    if (!mounted) return;
    setState(() => _removingKeys.remove(key));
  }

  @override
  Widget build(BuildContext context) {
    final cartState = ref.watch(cartProvider);

    final body = RefreshIndicator(
      color: const Color(0xFF6D0E16),
      onRefresh: () async => ref.read(cartProvider.notifier).fetchCart(),
      child: CustomScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        slivers: [
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(
                AppDimensions.screenPadding,
                AppDimensions.screenPadding,
                AppDimensions.screenPadding,
                0,
              ),
              child: _buildCartItems(cartState),
            ),
          ),
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(
                AppDimensions.screenPadding,
                AppDimensions.sectionGap,
                AppDimensions.screenPadding,
                0,
              ),
              child: _buildCouponPanel(cartState),
            ),
          ),
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(
                AppDimensions.screenPadding,
                AppDimensions.sectionGap,
                AppDimensions.screenPadding,
                AppDimensions.bottomSafeGap - 10,
              ),
              child: _buildSummaryPanel(context, cartState),
            ),
          ),
        ],
      ),
    );

    if (cartState.isLoading && cartState.items.isEmpty) {
      return widget.showAppBar
          ? Scaffold(
              appBar: _header(cartState),
              body: Skeletonizer(enabled: true, child: body),
            )
          : Skeletonizer(enabled: true, child: body);
    }

    if (cartState.items.isEmpty) {
      final empty = _buildEmptyState(context);
      return widget.showAppBar
          ? Scaffold(appBar: _header(cartState), body: empty)
          : empty;
    }

    return widget.showAppBar
        ? Scaffold(appBar: _header(cartState), body: body)
        : body;
  }

  PreferredSizeWidget _header(CartState cartState) {
    return AppBar(
      toolbarHeight: AppDimensions.appBarHeight,
      backgroundColor: Colors.white.withValues(alpha: 0.86),
      elevation: 0,
      centerTitle: true,
      iconTheme: const IconThemeData(color: Color(0xFF6D0E16)),
      title: Text(
        cartState.count > 0 ? 'السلة (${cartState.count})' : 'السلة',
        style: GoogleFonts.notoSerif(
          color: const Color(0xFF6D0E16),
          fontWeight: FontWeight.bold,
          fontSize: 20,
        ),
      ),
    );
  }

  Widget _buildCartItems(CartState cartState) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Text(
              'المنتجات',
              style: GoogleFonts.notoSerif(
                fontSize: 22,
                fontWeight: FontWeight.bold,
                color: const Color(0xFF6D0E16),
              ),
            ),
            const Spacer(),
            Text(
              'عرض الكل',
              style: GoogleFonts.manrope(
                fontSize: 12,
                fontWeight: FontWeight.w800,
                color: const Color(0xFFD59E06),
              ),
            ),
          ],
        ),
        const SizedBox(height: 14),
        ...cartState.items.map((item) {
          final isRemoving = _removingKeys.contains(item.selectionKey);

          return KeyedSubtree(
            key: ValueKey(item.selectionKey),
            child: _CartItemCard(
              item: item,
              isRemoving: isRemoving,
              onDecrease: isRemoving
                  ? null
                  : (item.quantity > 1
                        ? () => ref
                              .read(cartProvider.notifier)
                              .updateQuantity(
                                item.selectionKey,
                                item.quantity - 1,
                              )
                        : null),
              onIncrease: isRemoving
                  ? () {}
                  : () => ref
                        .read(cartProvider.notifier)
                        .updateQuantity(item.selectionKey, item.quantity + 1),
              onDelete: () => _removeItemWithAnimation(item.selectionKey),
            ),
          );
        }),
      ],
    );
  }

  Widget _buildCouponPanel(CartState cartState) {
    return Column(
      children: [
        Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(24),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.04),
                blurRadius: 18,
                offset: const Offset(0, 8),
              ),
            ],
          ),
          child: Row(
            children: [
              Expanded(
                child: TextField(
                  controller: _couponCtrl,
                  enabled: !cartState.isLoading,
                  decoration: InputDecoration(
                    hintText: 'أدخل كود الخصم',
                    filled: true,
                    fillColor: const Color(0xFFF5F2F1),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(16),
                      borderSide: BorderSide.none,
                    ),
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(16),
                      borderSide: BorderSide.none,
                    ),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(16),
                      borderSide: const BorderSide(
                        color: Color(0xFFD59E06),
                        width: 1,
                      ),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 10),
              FilledButton(
                onPressed: cartState.isLoading
                    ? null
                    : () {
                        if (_couponCtrl.text.trim().isNotEmpty) {
                          ref
                              .read(cartProvider.notifier)
                              .applyDiscount(_couponCtrl.text.trim());
                        }
                      },
                style: FilledButton.styleFrom(
                  backgroundColor: const Color(0xFF6D0E16),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 16),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(16),
                  ),
                ),
                child: Text(
                  'تطبيق',
                  style: GoogleFonts.manrope(fontWeight: FontWeight.w800),
                ),
              ),
            ],
          ),
        ),
        // عرض معلومات الخصم الذكي
        if (cartState.discount > 0) ...[
          const SizedBox(height: 12),
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: const Color(0xFFE8F5E9),
              borderRadius: BorderRadius.circular(16),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'كود الخصم المطبق',
                        style: GoogleFonts.manrope(
                          fontSize: 12,
                          color: const Color(0xFF666666),
                        ),
                      ),
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          Text(
                            cartState.discountCode ?? 'بدون كود',
                            style: GoogleFonts.manrope(
                              fontSize: 14,
                              fontWeight: FontWeight.bold,
                              color: const Color(0xFF0E7A5E),
                            ),
                          ),
                          const SizedBox(width: 8),
                          Text(
                            '(${cartState.discountPercentage.toStringAsFixed(1)}%)',
                            style: GoogleFonts.manrope(
                              fontSize: 12,
                              color: const Color(0xFF0E7A5E),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
                IconButton(
                  onPressed: cartState.isLoading
                      ? null
                      : () {
                          ref
                              .read(cartProvider.notifier)
                              .removeDiscount();
                          _couponCtrl.clear();
                        },
                  icon: const Icon(
                    Icons.close,
                    color: Color(0xFFE53935),
                    size: 20,
                  ),
                  tooltip: 'حذف الخصم',
                ),
              ],
            ),
          ),
        ],
      ],
    );
  }

  Widget _buildSummaryPanel(BuildContext context, CartState cartState) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: const Color(0xFFFEFEFE),
        borderRadius: BorderRadius.circular(28),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.06),
            blurRadius: 24,
            offset: const Offset(0, 10),
          ),
        ],
      ),
      child: Column(
        children: [
          _SummaryRow(
            label: 'المجموع الفرعي',
            value: _formatPrice(cartState.subtotal),
          ),
          if (cartState.discount > 0) ...[
            const SizedBox(height: 10),
            _SummaryRow(
              label: 'الخصم',
              value: '-${_formatPrice(cartState.discount)}',
              valueColor: const Color(0xFF0E7A5E),
            ),
          ],
          const SizedBox(height: 14),
          Container(height: 1, color: const Color(0xFFEDE8E7)),
          const SizedBox(height: 14),
          _SummaryRow(
            label: 'الإجمالي الكلي',
            value: _formatPrice(cartState.total),
            labelStyle: GoogleFonts.notoSerif(
              fontWeight: FontWeight.bold,
              fontSize: 18,
              color: const Color(0xFF1A1C1C),
            ),
            valueStyle: GoogleFonts.manrope(
              fontWeight: FontWeight.w800,
              fontSize: 18,
              color: const Color(0xFF6D0E16),
            ),
          ),
          const SizedBox(height: 16),
          SizedBox(
            width: double.infinity,
            child: FilledButton.icon(
              onPressed: () => context.push('/checkout'),
              icon: const Icon(Icons.arrow_back),
              label: Text(
                'إتمام الطلب',
                style: GoogleFonts.manrope(fontWeight: FontWeight.w800),
              ),
              style: FilledButton.styleFrom(
                backgroundColor: const Color(0xFF6D0E16),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 16),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(18),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyState(BuildContext context) {
    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.all(16),
      children: [
        Container(
          padding: const EdgeInsets.all(24),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(28),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.05),
                blurRadius: 20,
                offset: const Offset(0, 8),
              ),
            ],
          ),
          child: Column(
            children: [
              Container(
                width: 84,
                height: 84,
                decoration: BoxDecoration(
                  color: const Color(0xFF6D0E16).withValues(alpha: 0.08),
                  shape: BoxShape.circle,
                ),
                child: const Icon(
                  Icons.remove_shopping_cart_outlined,
                  size: 38,
                  color: Color(0xFF6D0E16),
                ),
              ),
              const SizedBox(height: 18),
              Text(
                'سلة المشتريات فارغة',
                style: GoogleFonts.notoSerif(
                  fontSize: 22,
                  fontWeight: FontWeight.bold,
                  color: const Color(0xFF6D0E16),
                ),
              ),
              const SizedBox(height: 8),
              Text(
                'أضف بعض المنتجات الفاخرة إلى السلة للمتابعة إلى إتمام الطلب.',
                textAlign: TextAlign.center,
                style: GoogleFonts.manrope(
                  fontSize: 13,
                  height: 1.6,
                  color: Colors.black54,
                ),
              ),
              const SizedBox(height: 18),
              FilledButton(
                onPressed: () => context.go('/'),
                style: FilledButton.styleFrom(
                  backgroundColor: const Color(0xFF6D0E16),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(
                    horizontal: 22,
                    vertical: 14,
                  ),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(16),
                  ),
                ),
                child: Text(
                  'العودة للرئيسية',
                  style: GoogleFonts.manrope(fontWeight: FontWeight.w800),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}

class _CartItemCard extends StatelessWidget {
  final dynamic item;
  final bool isRemoving;
  final VoidCallback? onDecrease;
  final VoidCallback onIncrease;
  final VoidCallback onDelete;

  const _CartItemCard({
    required this.item,
    required this.isRemoving,
    required this.onDecrease,
    required this.onIncrease,
    required this.onDelete,
  });

  @override
  Widget build(BuildContext context) {
    final width = MediaQuery.sizeOf(context).width;
    final compact = width < 380;

    final imageSize = compact ? 74.0 : 88.0;
    final cardPadding = compact ? 12.0 : 14.0;
    final cardRadius = compact ? 20.0 : 24.0;
    final horizontalGap = compact ? 10.0 : 14.0;
    final deleteSize = compact ? 30.0 : 34.0;
    final quantityFont = compact ? 14.0 : 16.0;
    final titleFont = compact ? 15.0 : 17.0;

    return AnimatedAlign(
      duration: const Duration(milliseconds: 180),
      curve: Curves.easeOut,
      alignment: Alignment.topCenter,
      heightFactor: isRemoving ? 0.0 : 1.0,
      child: AnimatedOpacity(
        duration: const Duration(milliseconds: 160),
        curve: Curves.easeOut,
        opacity: isRemoving ? 0.0 : 1.0,
        child: AnimatedSlide(
          duration: const Duration(milliseconds: 180),
          curve: Curves.easeOut,
          offset: isRemoving ? const Offset(0, -0.08) : Offset.zero,
          child: ClipRect(
            child: Container(
              margin: const EdgeInsets.only(bottom: 14),
              padding: EdgeInsets.all(cardPadding),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(cardRadius),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.04),
                    blurRadius: 18,
                    offset: const Offset(0, 8),
                  ),
                ],
              ),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  ClipRRect(
                    borderRadius: BorderRadius.circular(compact ? 14 : 18),
                    child: Image.network(
                      item.imageUrl.isNotEmpty
                          ? item.imageUrl
                          : 'https://placehold.co/160x160',
                      width: imageSize,
                      height: imageSize,
                      fit: BoxFit.cover,
                      errorBuilder: (c, e, s) => Container(
                        width: imageSize,
                        height: imageSize,
                        color: const Color(0xFFF4F0EF),
                        child: const Icon(Icons.image, color: Colors.grey),
                      ),
                    ),
                  ),
                  SizedBox(width: horizontalGap),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Expanded(
                              child: Text(
                                item.name,
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                                style: GoogleFonts.notoSerif(
                                  fontSize: titleFont,
                                  fontWeight: FontWeight.bold,
                                  color: const Color(0xFF1A1C1C),
                                ),
                              ),
                            ),
                            InkWell(
                              onTap: isRemoving ? null : onDelete,
                              child: Container(
                                width: deleteSize,
                                height: deleteSize,
                                decoration: BoxDecoration(
                                  color: const Color(
                                    0xFF6D0E16,
                                  ).withValues(alpha: 0.08),
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: Icon(
                                  Icons.delete_outline,
                                  size: compact ? 16 : 18,
                                  color: const Color(0xFF6D0E16),
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 6),
                        Text(
                          _formatPrice(item.price),
                          style: GoogleFonts.manrope(
                            fontSize: compact ? 12 : 13,
                            fontWeight: FontWeight.w800,
                            color: const Color(0xFFD59E06),
                          ),
                        ),
                        const SizedBox(height: 10),
                        Row(
                          children: [
                            _QuantityButton(
                              icon: Icons.remove,
                              onTap: isRemoving ? null : onDecrease,
                              compact: compact,
                            ),
                            Padding(
                              padding: EdgeInsets.symmetric(
                                horizontal: compact ? 10 : 12,
                              ),
                              child: Text(
                                '${item.quantity}',
                                style: GoogleFonts.manrope(
                                  fontSize: quantityFont,
                                  fontWeight: FontWeight.w800,
                                ),
                              ),
                            ),
                            _QuantityButton(
                              icon: Icons.add,
                              onTap: isRemoving ? null : onIncrease,
                              compact: compact,
                            ),
                            const Spacer(),
                            Text(
                              _formatPrice(item.total),
                              style: GoogleFonts.manrope(
                                fontSize: compact ? 12 : 13,
                                fontWeight: FontWeight.w800,
                                color: const Color(0xFF6D0E16),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
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

class _QuantityButton extends StatelessWidget {
  final IconData icon;
  final VoidCallback? onTap;
  final bool compact;

  const _QuantityButton({
    required this.icon,
    required this.onTap,
    this.compact = false,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(14),
      child: Container(
        width: compact ? 30 : 34,
        height: compact ? 30 : 34,
        decoration: BoxDecoration(
          color: const Color(0xFFF5F2F1),
          borderRadius: BorderRadius.circular(14),
        ),
        child: Icon(
          icon,
          size: compact ? 16 : 18,
          color: const Color(0xFF6D0E16),
        ),
      ),
    );
  }
}

class _SummaryRow extends StatelessWidget {
  final String label;
  final String value;
  final TextStyle? labelStyle;
  final TextStyle? valueStyle;
  final Color? valueColor;

  const _SummaryRow({
    required this.label,
    required this.value,
    this.labelStyle,
    this.valueStyle,
    this.valueColor,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Expanded(
          child: Text(
            label,
            style:
                labelStyle ??
                GoogleFonts.manrope(
                  fontSize: 13,
                  fontWeight: FontWeight.w600,
                  color: Colors.black87,
                ),
          ),
        ),
        Text(
          value,
          style:
              valueStyle ??
              GoogleFonts.manrope(
                fontSize: 13,
                fontWeight: FontWeight.w800,
                color: valueColor ?? const Color(0xFF1A1C1C),
              ),
        ),
      ],
    );
  }
}

String _formatPrice(num value) {
  return '${NumberFormat('#,##0.00').format(value)} د.ع';
}
