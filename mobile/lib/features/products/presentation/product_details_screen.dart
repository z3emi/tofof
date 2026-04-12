import 'dart:ui' as ui;

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_html/flutter_html.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:skeletonizer/skeletonizer.dart';

import '../../cart/providers/cart_provider.dart';
import '../../home/providers/store_provider.dart';
import '../../../shared/models/cart_item_model.dart';
import '../../../shared/models/product_model.dart';
import '../providers/wishlist_provider.dart';

class ProductDetailsScreen extends ConsumerStatefulWidget {
  final int productId;

  const ProductDetailsScreen({super.key, required this.productId});

  @override
  ConsumerState<ProductDetailsScreen> createState() =>
      _ProductDetailsScreenState();
}

class _ProductDetailsScreenState extends ConsumerState<ProductDetailsScreen> {
  final int _quantity = 1;
  int _galleryIndex = 0;
  final Map<int, ProductOptionValueModel> _selectedOptionValues = {};

  /// Helper function للترجمة
  String _tr(bool isArabic, String ar, String en) {
    return isArabic ? ar : en;
  }

  /// Helper function لتنسيق الأسعار بفواصل الآلاف
  String _formatPrice(double price) {
    final formatter = NumberFormat('#,##0', 'en_US');
    return formatter.format(price.toInt());
  }

  @override
  Widget build(BuildContext context) {
    final productAsync = ref.watch(productDetailsProvider(widget.productId));
    final cartState = ref.watch(cartProvider);
    final wishlist = ref.watch(wishlistProvider);
    final inWishlist = wishlist.contains(widget.productId);
    
    final isArabic = Localizations.localeOf(context).languageCode == 'ar';

    return Scaffold(
      body: productAsync.when(
        data: (product) => CustomScrollView(
          slivers: [
            SliverAppBar(
              expandedHeight: _galleryExpandedHeight(context, product),
              pinned: true,
              leadingWidth: 64,
              leading: Padding(
                padding: const EdgeInsets.all(8),
                child: _TopCircleIconButton(
                  icon: Icons.arrow_back_ios_new,
                  onTap: () => Navigator.of(context).maybePop(),
                ),
              ),
              flexibleSpace: FlexibleSpaceBar(
                background: _buildProductGallery(product),
              ),
              actions: [
                Padding(
                  padding: const EdgeInsets.all(8),
                  child: _TopCircleIconButton(
                    icon: inWishlist ? Icons.favorite : Icons.favorite_border,
                    iconColor: inWishlist ? Colors.red : const Color(0xFF2A2A2A),
                    onTap: () async {
                      try {
                        await ref
                            .read(wishlistProvider.notifier)
                            .toggle(widget.productId);
                      } catch (e) {
                        if (!context.mounted) return;
                        ScaffoldMessenger.of(
                          context,
                        ).showSnackBar(SnackBar(content: Text(e.toString())));
                      }
                    },
                  ),
                ),
              ],
            ),
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.all(16.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      product.localizedName(isArabic),
                      style: const TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        const Icon(Icons.star, color: Colors.orange, size: 20),
                        const SizedBox(width: 4),
                        Text(
                          '${product.averageRating.toStringAsFixed(1)} (${product.reviewsCount} ${_tr(isArabic, 'تقييم', 'ratings')})',
                          style: const TextStyle(color: Colors.grey),
                        ),
                        const Spacer(),
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 10,
                            vertical: 4,
                          ),
                          decoration: BoxDecoration(
                            color: product.stockQuantity > 0
                                ? Colors.green.withValues(alpha: 0.12)
                                : Colors.red.withValues(alpha: 0.12),
                            borderRadius: BorderRadius.circular(30),
                          ),
                          child: Text(
                            product.stockQuantity > 0
                                ? _tr(isArabic, 'متوفر', 'In Stock')
                                : _tr(isArabic, 'غير متوفر', 'Out of Stock'),
                            style: TextStyle(
                              color: product.stockQuantity > 0
                                  ? Colors.green
                                  : Colors.red,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),
                    Wrap(
                      spacing: 10,
                      crossAxisAlignment: WrapCrossAlignment.center,
                      children: [
                        Text(
                          '${_formatPrice(product.currentPrice)} ${_tr(isArabic, 'د.ع', 'IQD')}',
                          style: const TextStyle(
                            fontSize: 26,
                            color: Color(0xFF6D0E16),
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        if (product.isOnSale)
                          Text(
                            '${_formatPrice(product.price)} ${_tr(isArabic, 'د.ع', 'IQD')}',
                            style: const TextStyle(
                              fontSize: 18,
                              color: Colors.grey,
                              decoration: TextDecoration.lineThrough,
                            ),
                          ),
                      ],
                    ),
                    const SizedBox(height: 24),
                    if (product.options.isNotEmpty) ...[
                      Text(
                        _tr(isArabic, 'خيارات المنتج', 'Product Options'),
                        style: const TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 10),
                      ...product.options.map((option) {
                        final selected = _selectedOptionValues[option.id];

                        return Padding(
                          padding: const EdgeInsets.only(bottom: 12),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                option.isRequired
                                    ? '${option.name} *'
                                    : option.name,
                                style: const TextStyle(
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                              const SizedBox(height: 8),
                              Wrap(
                                spacing: 8,
                                runSpacing: 8,
                                children: option.values.map((value) {
                                  final isSelected = selected?.id == value.id;
                                  return ChoiceChip(
                                    label: Text(value.value),
                                    selected: isSelected,
                                    onSelected: (_) {
                                      setState(() {
                                        _selectedOptionValues[option.id] =
                                            value;
                                      });
                                    },
                                  );
                                }).toList(),
                              ),
                            ],
                          ),
                        );
                      }),
                      const SizedBox(height: 12),
                    ],
                    Text(
                      _tr(isArabic, 'وصف المنتج', 'Product Description'),
                      style: const TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Directionality(
                      textDirection: ui.TextDirection.rtl,
                      child: Html(
                        data: (product.localizedDescription(isArabic)?.trim().isNotEmpty == true)
                            ? product.localizedDescription(isArabic)
                            : '<p>${_tr(isArabic, 'لا يوجد وصف متاح', 'No description available.')}</p>',
                        style: {
                          'body': Style(
                            fontSize: FontSize(14),
                            lineHeight: const LineHeight(1.7),
                            margin: Margins.zero,
                            padding: HtmlPaddings.zero,
                            color: Colors.black87,
                          ),
                          'p': Style(margin: Margins.only(bottom: 10)),
                          'blockquote': Style(
                            margin: Margins.symmetric(vertical: 10),
                            padding: HtmlPaddings.all(12),
                            backgroundColor: const Color(0xFFF8F5F6),
                            border: Border(
                              left: BorderSide(
                                color: Color(0xFF6D0E16),
                                width: 4,
                              ),
                            ),
                          ),
                        },
                      ),
                    ),
                    const SizedBox(height: 24),
                    Text(
                      _tr(isArabic, 'التعليقات والتقييمات', 'Reviews & Ratings'),
                      style: const TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 10),
                    ..._buildReviews(product, isArabic),
                    const SizedBox(height: 120),
                  ],
                ),
              ),
            ),
          ],
        ),
        loading: () => Skeletonizer(enabled: true, child: _buildSkeletonPage()),
        error: (e, s) => Center(
          child: Padding(
            padding: const EdgeInsets.all(24),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(
                  Icons.wifi_off_rounded,
                  size: 56,
                  color: Color(0xFF6D0E16),
                ),
                const SizedBox(height: 12),
                Text(
                  _tr(isArabic, 'تعذر تحميل المنتج الآن', 'Failed to load product'),
                  style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 8),
                Text(
                  _tr(isArabic, 'تأكد من اتصال الإنترنت أو من عنوان الخادم، ثم أعد المحاولة.', 'Check your internet connection, then try again.'),
                  textAlign: TextAlign.center,
                  style: TextStyle(color: Colors.grey.shade700, height: 1.5),
                ),
                const SizedBox(height: 16),
                OutlinedButton(
                  onPressed: () =>
                      ref.invalidate(productDetailsProvider(widget.productId)),
                  child: Text(_tr(isArabic, 'إعادة المحاولة', 'Retry')),
                ),
              ],
            ),
          ),
        ),
      ),
      bottomSheet: productAsync.hasValue
          ? _buildBottomSheet(
              context,
              productAsync.value,
              isArabic,
              _findCartItem(cartState.items),
            )
          : null,
    );
  }

  CartItemModel? _findCartItem(List<CartItemModel> items) {
    for (final item in items) {
      if (item.productId == widget.productId) {
        return item;
      }
    }
    return null;
  }

  Widget _buildBottomSheet(
    BuildContext context,
    ProductModel? product,
    bool isArabic,
    CartItemModel? cartItem,
  ) {
    final notifier = ref.read(cartProvider.notifier);

    if (product == null) {
      return const SizedBox.shrink();
    }

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.surface,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
            offset: const Offset(0, -5),
          ),
        ],
      ),
      child: AnimatedSize(
        duration: const Duration(milliseconds: 220),
        curve: Curves.easeOut,
        child: AnimatedSwitcher(
          duration: const Duration(milliseconds: 220),
          switchInCurve: Curves.easeOut,
          switchOutCurve: Curves.easeIn,
          transitionBuilder: (child, animation) {
            final fade = CurvedAnimation(parent: animation, curve: Curves.easeOut);
            final slide = Tween<Offset>(
              begin: const Offset(0, 0.08),
              end: Offset.zero,
            ).animate(fade);
            return FadeTransition(
              opacity: fade,
              child: SlideTransition(position: slide, child: child),
            );
          },
          child: cartItem == null
              ? SizedBox(
                  key: const ValueKey('add_button_mode'),
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: () async {
                      final missingRequired = product.options
                          .where((option) => option.isRequired)
                          .where(
                            (option) =>
                                !_selectedOptionValues.containsKey(option.id),
                          )
                          .toList();

                      if (missingRequired.isNotEmpty) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(
                              _tr(isArabic, 'يرجى اختيار: ', 'Please select: ') +
                                  missingRequired.map((e) => e.name).join('، '),
                            ),
                            backgroundColor: Colors.orange,
                          ),
                        );
                        return;
                      }

                      final selectedOptions = <String, dynamic>{};
                      for (final option in product.options) {
                        final selected = _selectedOptionValues[option.id];
                        if (selected != null) {
                          selectedOptions[option.name] = selected.value;
                        }
                      }

                      final success = await notifier.addToCart(
                        widget.productId,
                        _quantity,
                        selectedOptions.isEmpty ? null : selectedOptions,
                      );
                      if (!context.mounted) return;
                      if (!success) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(
                              _tr(
                                isArabic,
                                'تعذر إضافة المنتج',
                                'Failed to add product',
                              ),
                            ),
                            backgroundColor: Colors.red,
                          ),
                        );
                      }
                    },
                    child: Text(_tr(isArabic, 'أضف إلى السلة', 'Add to Cart')),
                  ),
                )
              : Column(
                  key: const ValueKey('cart_controls_mode'),
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Container(
                          decoration: BoxDecoration(
                            border: Border.all(color: Colors.grey.shade300),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Row(
                            children: [
                              IconButton(
                                icon: const Icon(Icons.remove),
                                onPressed: cartItem.quantity > 1
                                    ? () => notifier.updateQuantity(
                                          cartItem.selectionKey,
                                          cartItem.quantity - 1,
                                        )
                                    : null,
                              ),
                              Text(
                                '${cartItem.quantity}',
                                style: const TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              IconButton(
                                icon: const Icon(Icons.add),
                                onPressed: () => notifier.updateQuantity(
                                  cartItem.selectionKey,
                                  cartItem.quantity + 1,
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 12),
                        IconButton.filledTonal(
                          onPressed: () =>
                              notifier.removeItem(cartItem.selectionKey),
                          style: IconButton.styleFrom(
                            backgroundColor: const Color(0xFFFFEBEE),
                            foregroundColor: const Color(0xFFC62828),
                          ),
                          icon: const Icon(Icons.delete_outline),
                          tooltip: _tr(
                            isArabic,
                            'حذف من السلة',
                            'Remove from cart',
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 10),
                    SizedBox(
                      width: double.infinity,
                      child: OutlinedButton.icon(
                        onPressed: () => context.push('/cart'),
                        icon: const Icon(Icons.shopping_cart_outlined),
                        label: Text(_tr(isArabic, 'عرض السلة', 'View Cart')),
                        style: OutlinedButton.styleFrom(
                          side: BorderSide(color: Colors.grey.shade300),
                          foregroundColor: const Color(0xFF6D0E16),
                          padding: const EdgeInsets.symmetric(vertical: 10),
                        ),
                      ),
                    ),
                  ],
                ),
        ),
      ),
    );
  }

  Widget _buildProductGallery(ProductModel product) {
    final images = {
      if (product.imageUrl != null) product.imageUrl!,
      ...product.images,
    }.toList();

    if (images.isEmpty) {
      return Container(
        color: Colors.grey[200],
        child: const Icon(Icons.image, size: 80),
      );
    }

    final current = images[_galleryIndex.clamp(0, images.length - 1)];

    return Container(
      color: const Color(0xFFEDEDED),
      child: SafeArea(
        bottom: false,
        child: Padding(
          padding: const EdgeInsets.fromLTRB(12, 12, 12, 14),
          child: Column(
            children: [
              AspectRatio(
                aspectRatio: 1,
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(10),
                  child: Container(
                    color: const Color(0xFF111111),
                    child: Image.network(
                      current,
                      fit: BoxFit.contain,
                      width: double.infinity,
                      errorBuilder: (context, error, stackTrace) => Container(
                        color: Colors.grey[200],
                        child: const Icon(Icons.broken_image, size: 80),
                      ),
                    ),
                  ),
                ),
              ),
              if (images.length > 1) ...[
                const SizedBox(height: 14),
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: [
                      for (int index = 0; index < images.length; index++) ...[
                        if (index > 0) const SizedBox(width: 10),
                        _GalleryThumb(
                          image: images[index],
                          active: _galleryIndex == index,
                          onTap: () => setState(() => _galleryIndex = index),
                        ),
                      ],
                    ],
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  double _galleryExpandedHeight(BuildContext context, ProductModel product) {
    final imageWidth = MediaQuery.sizeOf(context).width - 24;
    final hasThumbs = {
          if (product.imageUrl != null) product.imageUrl!,
          ...product.images,
        }.length >
        1;
    final thumbsBlock = hasThumbs ? 106.0 : 26.0;
    final topSafe = MediaQuery.paddingOf(context).top;
    return topSafe + imageWidth + thumbsBlock;
  }

  List<Widget> _buildReviews(ProductModel product, bool isArabic) {
    if (product.reviews.isEmpty) {
      return [
        Container(
          width: double.infinity,
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(14),
            color: Colors.grey.withValues(alpha: 0.08),
          ),
          child: Text(
            _tr(isArabic, 'لا توجد تعليقات حتى الآن. كن أول من يشارك رأيه.', 'No reviews yet. Be the first to share your opinion.'),
          ),
        ),
      ];
    }

    return product.reviews.map((review) {
      return Container(
        width: double.infinity,
        margin: const EdgeInsets.only(bottom: 12),
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(14),
          color: Theme.of(context).colorScheme.surface,
          border: Border.all(color: Colors.grey.withValues(alpha: 0.2)),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Text(
                    review.author,
                    style: const TextStyle(fontWeight: FontWeight.bold),
                  ),
                ),
                Text(
                  _formatReviewDate(review.createdAt),
                  style: const TextStyle(color: Colors.grey, fontSize: 12),
                ),
              ],
            ),
            const SizedBox(height: 6),
            Row(
              children: List.generate(
                5,
                (index) => Icon(
                  index < review.rating.round()
                      ? Icons.star
                      : Icons.star_border,
                  color: Colors.orange,
                  size: 17,
                ),
              ),
            ),
            if (review.comment.trim().isNotEmpty) ...[
              const SizedBox(height: 8),
              Text(review.comment, style: const TextStyle(height: 1.5)),
            ],
          ],
        ),
      );
    }).toList();
  }

  Widget _buildSkeletonPage() {
    return CustomScrollView(
      slivers: [
        SliverAppBar(
          expandedHeight: 360,
          pinned: true,
          flexibleSpace: Container(color: Colors.grey[300]),
        ),
        SliverToBoxAdapter(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(height: 28, width: 220, color: Colors.white),
                const SizedBox(height: 10),
                Container(height: 18, width: 140, color: Colors.white),
                const SizedBox(height: 16),
                Container(height: 30, width: 130, color: Colors.white),
                const SizedBox(height: 24),
                Container(height: 22, width: 110, color: Colors.white),
                const SizedBox(height: 8),
                Container(
                  height: 14,
                  width: double.infinity,
                  color: Colors.white,
                ),
                const SizedBox(height: 6),
                Container(
                  height: 14,
                  width: double.infinity,
                  color: Colors.white,
                ),
                const SizedBox(height: 30),
                Container(height: 22, width: 130, color: Colors.white),
                const SizedBox(height: 8),
                ...List.generate(
                  3,
                  (_) => Container(
                    margin: const EdgeInsets.only(bottom: 10),
                    height: 94,
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(14),
                      color: Colors.white,
                    ),
                  ),
                ),
                const SizedBox(height: 120),
              ],
            ),
          ),
        ),
      ],
    );
  }

  String _formatReviewDate(String? rawDate) {
    if (rawDate == null || rawDate.trim().isEmpty) {
      return Localizations.localeOf(context).languageCode == 'ar' ? 'حديثًا' : 'Recently';
    }

    final date = DateTime.tryParse(rawDate);
    if (date == null) return Localizations.localeOf(context).languageCode == 'ar' ? 'حديثًا' : 'Recently';

    return DateFormat('yyyy/MM/dd').format(date);
  }
}

class _TopCircleIconButton extends StatelessWidget {
  final IconData icon;
  final Color? iconColor;
  final VoidCallback onTap;

  const _TopCircleIconButton({
    required this.icon,
    this.iconColor,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(999),
        child: Ink(
          width: 44,
          height: 44,
          decoration: BoxDecoration(
            color: Colors.white.withValues(alpha: 0.78),
            shape: BoxShape.circle,
            border: Border.all(color: Colors.white.withValues(alpha: 0.7)),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.08),
                blurRadius: 10,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: Icon(icon, color: iconColor ?? const Color(0xFF2A2A2A)),
        ),
      ),
    );
  }
}

class _GalleryThumb extends StatelessWidget {
  final String image;
  final bool active;
  final VoidCallback onTap;

  const _GalleryThumb({
    required this.image,
    required this.active,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 180),
        width: 66,
        height: 66,
        padding: const EdgeInsets.all(4),
        decoration: BoxDecoration(
          color: const Color(0xFF9E9E9E).withValues(alpha: 0.45),
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: active ? const Color(0xFF6D0E16) : Colors.transparent,
            width: 2,
          ),
        ),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(12),
          child: Image.network(
            image,
            fit: BoxFit.cover,
            errorBuilder: (context, error, stackTrace) => Container(
              color: Colors.grey.shade300,
              child: const Icon(Icons.image_outlined),
            ),
          ),
        ),
      ),
    );
  }
}
