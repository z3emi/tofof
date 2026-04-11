import 'dart:ui' as ui;

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_html/flutter_html.dart';
import 'package:intl/intl.dart';
import 'package:skeletonizer/skeletonizer.dart';

import '../../cart/providers/cart_provider.dart';
import '../../home/providers/store_provider.dart';
import '../../../shared/models/product_model.dart';
import '../providers/wishlist_provider.dart';

class ProductDetailsScreen extends ConsumerStatefulWidget {
  final int productId;

  const ProductDetailsScreen({super.key, required this.productId});

  @override
  ConsumerState<ProductDetailsScreen> createState() => _ProductDetailsScreenState();
}

class _ProductDetailsScreenState extends ConsumerState<ProductDetailsScreen> {
  int _quantity = 1;
  int _galleryIndex = 0;

  @override
  Widget build(BuildContext context) {
    final productAsync = ref.watch(productDetailsProvider(widget.productId));
    final wishlist = ref.watch(wishlistProvider);
    final inWishlist = wishlist.contains(widget.productId);

    return Scaffold(
      body: productAsync.when(
        data: (product) => CustomScrollView(
          slivers: [
            SliverAppBar(
              expandedHeight: 360.0,
              pinned: true,
              flexibleSpace: FlexibleSpaceBar(
                background: _buildProductGallery(product),
              ),
              actions: [
                IconButton(
                  icon: Icon(inWishlist ? Icons.favorite : Icons.favorite_border, color: inWishlist ? Colors.red : null),
                  onPressed: () async {
                    try {
                      final added = await ref.read(wishlistProvider.notifier).toggle(widget.productId);
                      if (!context.mounted) return;
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(content: Text(added ? 'تمت الإضافة للمفضلة' : 'تمت الإزالة من المفضلة')),
                      );
                    } catch (e) {
                      if (!context.mounted) return;
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(content: Text(e.toString())),
                      );
                    }
                  },
                ),
              ],
            ),
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.all(16.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(product.name, style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        const Icon(Icons.star, color: Colors.orange, size: 20),
                        const SizedBox(width: 4),
                        Text('${product.averageRating.toStringAsFixed(1)} (${product.reviewsCount} تقييم)', style: const TextStyle(color: Colors.grey)),
                        const Spacer(),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: product.stockQuantity > 0 ? Colors.green.withValues(alpha: 0.12) : Colors.red.withValues(alpha: 0.12),
                            borderRadius: BorderRadius.circular(30),
                          ),
                          child: Text(
                            product.stockQuantity > 0 ? 'متوفر' : 'غير متوفر',
                            style: TextStyle(
                              color: product.stockQuantity > 0 ? Colors.green : Colors.red,
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
                          '${product.currentPrice.toStringAsFixed(2)} د.ع',
                          style: const TextStyle(
                            fontSize: 26,
                            color: Color(0xFF6D0E16),
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        if (product.isOnSale)
                          Text(
                            '${product.price.toStringAsFixed(2)} د.ع',
                            style: const TextStyle(
                              fontSize: 18,
                              color: Colors.grey,
                              decoration: TextDecoration.lineThrough,
                            ),
                          ),
                      ],
                    ),
                    const SizedBox(height: 24),
                    const Text('وصف المنتج', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 8),
                    Directionality(
                      textDirection: ui.TextDirection.rtl,
                      child: Html(
                        data: product.description?.trim().isNotEmpty == true ? product.description! : '<p>لا يوجد وصف متاح.</p>',
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
                            border: Border(left: BorderSide(color: Color(0xFF6D0E16), width: 4)),
                          ),
                        },
                      ),
                    ),
                    const SizedBox(height: 24),
                    const Text('التعليقات والتقييمات', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 10),
                    ..._buildReviews(product),
                    const SizedBox(height: 120),
                  ],
                ),
              ),
            ),
          ],
        ),
        loading: () => Skeletonizer(
          enabled: true,
          child: _buildSkeletonPage(),
        ),
        error: (e, s) => Center(
          child: Padding(
            padding: const EdgeInsets.all(24),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(Icons.wifi_off_rounded, size: 56, color: Color(0xFF6D0E16)),
                const SizedBox(height: 12),
                const Text(
                  'تعذر تحميل المنتج الآن',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 8),
                Text(
                  'تأكد من اتصال الإنترنت أو من عنوان الخادم، ثم أعد المحاولة.',
                  textAlign: TextAlign.center,
                  style: TextStyle(color: Colors.grey.shade700, height: 1.5),
                ),
                const SizedBox(height: 16),
                OutlinedButton(
                  onPressed: () => ref.invalidate(productDetailsProvider(widget.productId)),
                  child: const Text('إعادة المحاولة'),
                ),
              ],
            ),
          ),
        ),
      ),
      bottomSheet: productAsync.hasValue
          ? Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Theme.of(context).colorScheme.surface,
                boxShadow: [
                  BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 10, offset: const Offset(0, -5))
                ],
              ),
              child: Row(
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
                          onPressed: () => setState(() {
                            if (_quantity > 1) _quantity--;
                          }),
                        ),
                        Text('$_quantity', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                        IconButton(
                          icon: const Icon(Icons.add),
                          onPressed: () => setState(() => _quantity++),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: ElevatedButton(
                      onPressed: () async {
                        final success = await ref.read(cartProvider.notifier).addToCart(widget.productId, _quantity);
                        if (!context.mounted) return;
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(success ? 'تمت الإضافة للسلة بنجاح' : 'تعذر إضافة المنتج'),
                            backgroundColor: success ? Colors.green : Colors.red,
                          ),
                        );
                      },
                      child: const Text('أضف إلى السلة'),
                    ),
                  )
                ],
              ),
            )
          : null,
    );
  }

  Widget _buildProductGallery(ProductModel product) {
    final images = {
      if (product.imageUrl != null) product.imageUrl!,
      ...product.images,
    }.toList();

    if (images.isEmpty) {
      return Container(color: Colors.grey[200], child: const Icon(Icons.image, size: 80));
    }

    final current = images[_galleryIndex.clamp(0, images.length - 1)];

    return Stack(
      fit: StackFit.expand,
      children: [
        Image.network(
          current,
          fit: BoxFit.cover,
          errorBuilder: (context, error, stackTrace) => Container(color: Colors.grey[200], child: const Icon(Icons.broken_image, size: 80)),
        ),
        if (images.length > 1)
          Positioned(
            right: 12,
            left: 12,
            bottom: 16,
            child: SizedBox(
              height: 62,
              child: ListView.separated(
                scrollDirection: Axis.horizontal,
                itemCount: images.length,
                separatorBuilder: (_, __) => const SizedBox(width: 8),
                itemBuilder: (context, index) {
                  final image = images[index];
                  final active = _galleryIndex == index;

                  return GestureDetector(
                    onTap: () => setState(() => _galleryIndex = index),
                    child: AnimatedContainer(
                      duration: const Duration(milliseconds: 180),
                      width: 62,
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: active ? Colors.white : Colors.white70, width: active ? 2 : 1),
                      ),
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(9),
                        child: Image.network(image, fit: BoxFit.cover),
                      ),
                    ),
                  );
                },
              ),
            ),
          )
      ],
    );
  }

  List<Widget> _buildReviews(ProductModel product) {
    if (product.reviews.isEmpty) {
      return [
        Container(
          width: double.infinity,
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(14),
            color: Colors.grey.withValues(alpha: 0.08),
          ),
          child: const Text('لا توجد تعليقات حتى الآن. كن أول من يشارك رأيه.'),
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
                Expanded(child: Text(review.author, style: const TextStyle(fontWeight: FontWeight.bold))),
                Text(_formatReviewDate(review.createdAt), style: const TextStyle(color: Colors.grey, fontSize: 12)),
              ],
            ),
            const SizedBox(height: 6),
            Row(
              children: List.generate(
                5,
                (index) => Icon(
                  index < review.rating.round() ? Icons.star : Icons.star_border,
                  color: Colors.orange,
                  size: 17,
                ),
              ),
            ),
            if (review.comment.trim().isNotEmpty) ...[
              const SizedBox(height: 8),
              Text(review.comment, style: const TextStyle(height: 1.5)),
            ]
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
                Container(height: 14, width: double.infinity, color: Colors.white),
                const SizedBox(height: 6),
                Container(height: 14, width: double.infinity, color: Colors.white),
                const SizedBox(height: 30),
                Container(height: 22, width: 130, color: Colors.white),
                const SizedBox(height: 8),
                ...List.generate(
                  3,
                  (_) => Container(
                    margin: const EdgeInsets.only(bottom: 10),
                    height: 94,
                    decoration: BoxDecoration(borderRadius: BorderRadius.circular(14), color: Colors.white),
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
      return 'حديثًا';
    }

    final date = DateTime.tryParse(rawDate);
    if (date == null) return 'حديثًا';

    return DateFormat('yyyy/MM/dd').format(date);
  }
}
