import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:skeletonizer/skeletonizer.dart';

import '../../cart/providers/cart_provider.dart';
import '../../home/providers/store_provider.dart';
import '../../wishlist/providers/wishlist_provider.dart';
import '../../../shared/models/product_model.dart';

class ProductDetailsScreen extends ConsumerStatefulWidget {
  final int productId;
  const ProductDetailsScreen({super.key, required this.productId});

  @override
  ConsumerState<ProductDetailsScreen> createState() => _ProductDetailsScreenState();
}

class _ProductDetailsScreenState extends ConsumerState<ProductDetailsScreen> {
  int _quantity = 1;
  int _currentImageIndex = 0;
  final PageController _pageCtrl = PageController();

  @override
  void dispose() {
    _pageCtrl.dispose();
    super.dispose();
  }

  // ─── dummy for skeleton ───────────────────────────────────────────────────
  static final _dummy = ProductModel(
    id: 0,
    name: 'اسم المنتج الجميل يظهر هنا',
    description:
        'وصف مفصل للمنتج يشرح مميزاته وخصائصه ومتى يجب استخدامه '
        'وكل المعلومات المهمة التي يحتاجها العميل.',
    price: 35000,
    currentPrice: 28000,
    isOnSale: true,
    salePrice: 28000,
    stockQuantity: 10,
    averageRating: 4.5,
    reviewsCount: 24,
    imageUrl: '',
    images: [],
    reviews: List.generate(
      2,
      (i) => ReviewModel(id: i, userName: 'مستخدم تجريبي', rating: 4, comment: 'منتج رائع وجودته عالية جداً'),
    ),
  );

  // ─────────────────────────────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    final productAsync = ref.watch(productDetailsProvider(widget.productId));

    return Scaffold(
      body: productAsync.when(
        data: (product) => _buildBody(context, product, false),
        loading: () => _buildBody(context, _dummy, true),
        error: (e, _) => Center(
          child: Padding(
            padding: const EdgeInsets.all(24),
            child: Text('تعذّر تحميل المنتج\n$e', textAlign: TextAlign.center),
          ),
        ),
      ),
      bottomSheet: productAsync.hasValue
          ? _buildBottomSheet(productAsync.value!)
          : null,
    );
  }

  Widget _buildBody(BuildContext context, ProductModel product, bool loading) {
    final allImages = product.images.isNotEmpty
        ? product.images.map((i) => i.url).toList()
        : (product.imageUrl != null && product.imageUrl!.isNotEmpty
            ? [product.imageUrl!]
            : <String>[]);

    // wishlist state (only usable when not loading)
    final isFav = !loading && ref.watch(wishlistProvider).contains(product.id);

    return Skeletonizer(
      enabled: loading,
      child: CustomScrollView(
        slivers: [
          // ── Image Carousel SliverAppBar ─────────────────────────────────
          SliverAppBar(
            expandedHeight: 340,
            pinned: true,
            backgroundColor: Colors.white,
            foregroundColor: Colors.black,
            actions: [
              if (!loading)
                IconButton(
                  icon: Icon(
                    isFav ? Icons.favorite : Icons.favorite_border,
                    color: isFav ? Colors.red : null,
                  ),
                  onPressed: () => ref.read(wishlistProvider.notifier).toggle(product.id),
                ),
            ],
            flexibleSpace: FlexibleSpaceBar(
              background: Stack(
                children: [
                  // Image PageView
                  allImages.isEmpty
                      ? Container(
                          color: Colors.grey[200],
                          child: const Center(child: Icon(Icons.image, size: 80, color: Colors.grey)),
                        )
                      : PageView.builder(
                          controller: _pageCtrl,
                          itemCount: allImages.length,
                          onPageChanged: (i) => setState(() => _currentImageIndex = i),
                          itemBuilder: (_, i) => Image.network(
                            allImages[i],
                            fit: BoxFit.cover,
                            errorBuilder: (_, __, ___) => Container(
                              color: Colors.grey[200],
                              child: const Icon(Icons.broken_image, size: 60),
                            ),
                          ),
                        ),
                  // Dots Indicator
                  if (allImages.length > 1)
                    Positioned(
                      bottom: 12,
                      left: 0,
                      right: 0,
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: List.generate(
                          allImages.length,
                          (i) => AnimatedContainer(
                            duration: const Duration(milliseconds: 250),
                            margin: const EdgeInsets.symmetric(horizontal: 3),
                            width: i == _currentImageIndex ? 20 : 8,
                            height: 8,
                            decoration: BoxDecoration(
                              color: i == _currentImageIndex
                                  ? const Color(0xFF6D0E16)
                                  : Colors.white.withValues(alpha: 0.7),
                              borderRadius: BorderRadius.circular(4),
                            ),
                          ),
                        ),
                      ),
                    ),
                ],
              ),
            ),
          ),

          // ── Product Info ────────────────────────────────────────────────
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(16, 20, 16, 0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Name
                  Text(product.name,
                      style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 8),

                  // Rating row
                  Row(
                    children: [
                      ...List.generate(5, (i) {
                        final full = i < product.averageRating.floor();
                        final half = !full && i < product.averageRating;
                        return Icon(
                          full ? Icons.star : half ? Icons.star_half : Icons.star_border,
                          color: Colors.orange,
                          size: 18,
                        );
                      }),
                      const SizedBox(width: 6),
                      Text('${product.averageRating} (${product.reviewsCount} تقييم)',
                          style: TextStyle(color: Colors.grey[600], fontSize: 13)),
                    ],
                  ),
                  const SizedBox(height: 14),

                  // Price row
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Text(
                        '${product.currentPrice.toStringAsFixed(0)} د.ع',
                        style: const TextStyle(
                            fontSize: 26,
                            fontWeight: FontWeight.bold,
                            color: Color(0xFF6D0E16)),
                      ),
                      if (product.isOnSale) ...[
                        const SizedBox(width: 10),
                        Text(
                          '${product.price.toStringAsFixed(0)} د.ع',
                          style: TextStyle(
                            fontSize: 16,
                            color: Colors.grey[500],
                            decoration: TextDecoration.lineThrough,
                          ),
                        ),
                        const SizedBox(width: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: const Color(0xFF6D0E16).withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            '-${(((product.price - product.currentPrice) / product.price) * 100).toStringAsFixed(0)}%',
                            style: const TextStyle(color: Color(0xFF6D0E16), fontSize: 12, fontWeight: FontWeight.bold),
                          ),
                        )
                      ],
                    ],
                  ),
                  const SizedBox(height: 6),

                  // Stock badge
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: product.stockQuantity > 0 ? Colors.green[50] : Colors.red[50],
                      borderRadius: BorderRadius.circular(6),
                      border: Border.all(
                        color: product.stockQuantity > 0 ? Colors.green : Colors.red,
                        width: 0.8,
                      ),
                    ),
                    child: Text(
                      product.stockQuantity > 0
                          ? 'متوفر في المخزن (${product.stockQuantity})'
                          : 'نفد من المخزن',
                      style: TextStyle(
                        color: product.stockQuantity > 0 ? Colors.green[800] : Colors.red,
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),

                  if (product.category != null) ...[
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Icon(Icons.category_outlined, size: 15, color: Colors.grey[500]),
                        const SizedBox(width: 4),
                        Text(
                          product.category!['name_ar'] as String? ?? '',
                          style: TextStyle(color: Colors.grey[600], fontSize: 13),
                        ),
                      ],
                    )
                  ],

                  const Divider(height: 32),

                  // Description
                  const Text('وصف المنتج',
                      style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 8),
                  Text(
                    product.description ?? 'لا يوجد وصف متاح لهذا المنتج.',
                    style: TextStyle(fontSize: 14, color: Colors.grey[700], height: 1.6),
                  ),

                  const Divider(height: 32),

                  // Reviews Section
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text('تقييمات العملاء (${product.reviewsCount})',
                          style: const TextStyle(fontSize: 17, fontWeight: FontWeight.bold)),
                    ],
                  ),
                  const SizedBox(height: 12),
                  if (product.reviews.isEmpty && !loading)
                    Container(
                      padding: const EdgeInsets.all(20),
                      decoration: BoxDecoration(
                        color: Colors.grey[50],
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: Colors.grey[200]!),
                      ),
                      child: const Center(child: Text('لا توجد تقييمات بعد، كن أول من يُقيّم!')),
                    )
                  else
                    ...product.reviews.map((r) => _buildReviewTile(r)),

                  const SizedBox(height: 120), // space for bottom sheet
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildReviewTile(ReviewModel review) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.grey[50],
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey[200]!),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              CircleAvatar(
                radius: 18,
                backgroundColor: const Color(0xFF6D0E16).withValues(alpha: 0.15),
                backgroundImage: review.userAvatar != null && review.userAvatar!.isNotEmpty
                    ? NetworkImage(review.userAvatar!)
                    : null,
                child: review.userAvatar == null || review.userAvatar!.isEmpty
                    ? Text(
                        (review.userName ?? 'م').substring(0, 1),
                        style: const TextStyle(
                            color: Color(0xFF6D0E16), fontWeight: FontWeight.bold),
                      )
                    : null,
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(review.userName ?? 'مجهول',
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                    Row(
                      children: List.generate(5, (i) => Icon(
                        i < review.rating ? Icons.star : Icons.star_border,
                        color: Colors.orange,
                        size: 14,
                      )),
                    ),
                  ],
                ),
              ),
            ],
          ),
          if (review.comment != null && review.comment!.isNotEmpty) ...[
            const SizedBox(height: 8),
            Text(review.comment!, style: TextStyle(color: Colors.grey[700], fontSize: 13)),
          ],
        ],
      ),
    );
  }

  // ─── Bottom Sheet ─────────────────────────────────────────────────────────
  Widget _buildBottomSheet(ProductModel product) {
    return Container(
      padding: EdgeInsets.only(
        left: 16,
        right: 16,
        top: 16,
        bottom: MediaQuery.of(context).padding.bottom + 16,
      ),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.07),
            blurRadius: 20,
            offset: const Offset(0, -6),
          )
        ],
      ),
      child: Row(
        children: [
          // Quantity selector
          Container(
            decoration: BoxDecoration(
              border: Border.all(color: Colors.grey[300]!),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                _qtyButton(Icons.remove, () {
                  if (_quantity > 1) setState(() => _quantity--);
                }),
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 12),
                  child: Text('$_quantity',
                      style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                ),
                _qtyButton(Icons.add, () => setState(() => _quantity++)),
              ],
            ),
          ),
          const SizedBox(width: 12),
          // Add to cart
          Expanded(
            child: ElevatedButton.icon(
              style: ElevatedButton.styleFrom(
                padding: const EdgeInsets.symmetric(vertical: 15),
                backgroundColor: const Color(0xFF6D0E16),
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
              ),
              icon: const Icon(Icons.shopping_cart_checkout),
              label: const Text('أضف للسلة', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
              onPressed: product.stockQuantity <= 0
                  ? null
                  : () async {
                      final ok = await ref
                          .read(cartProvider.notifier)
                          .addToCart(product.id, _quantity);
                      if (!mounted) return;
                      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                        content: Text(ok ? '✅ تمت الإضافة للسلة' : '❌ تعذّر الإضافة'),
                        backgroundColor: ok ? Colors.green[700] : Colors.red[700],
                        behavior: SnackBarBehavior.floating,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ));
                    },
            ),
          ),
        ],
      ),
    );
  }

  Widget _qtyButton(IconData icon, VoidCallback onTap) {
    return InkWell(
      onTap: onTap,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
        child: Icon(icon, size: 20),
      ),
    );
  }
}
