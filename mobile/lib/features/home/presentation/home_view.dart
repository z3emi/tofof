import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:skeletonizer/skeletonizer.dart';

import '../providers/store_provider.dart';
import '../../../shared/models/product_model.dart';

class HomeView extends ConsumerWidget {
  const HomeView({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final slidersAsync = ref.watch(slidersProvider);
    final productsAsync = ref.watch(homeProductsProvider);

    return Scaffold(
      body: RefreshIndicator(
        onRefresh: () async {
          ref.invalidate(slidersProvider);
          ref.invalidate(homeProductsProvider);
        },
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Sliders Section
              slidersAsync.when(
                data: (data) => _buildSliders(data['hero'] ?? []),
                loading: () => Skeletonizer(child: _buildSliders(const [{'image_url': ''}])),
                error: (e, s) => const SizedBox(height: 200, child: Center(child: Text('خطأ في جلب العروض'))),
              ),

              const Padding(
                padding: EdgeInsets.all(16.0),
                child: Text('وصل حديثاً', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
              ),

              // Recent Products Section
              _buildLatestProducts(productsAsync),
              const SizedBox(height: 100),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSliders(List dynamicSlides) {
    if (dynamicSlides.isEmpty) return const SizedBox();
    return AspectRatio(
      aspectRatio: 2.2, // Matches typical wide store sliders
      child: PageView.builder(
        itemCount: dynamicSlides.length,
        itemBuilder: (context, index) {
          final slide = dynamicSlides[index];
          return Container(
            margin: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(12),
              image: slide['image_url'] != null && slide['image_url'].toString().isNotEmpty
                  ? DecorationImage(image: NetworkImage(slide['image_url']), fit: BoxFit.cover)
                  : null,
              color: Colors.grey[300],
            ),
          );
        },
      ),
    );
  }

  Widget _buildLatestProducts(AsyncValue<List<ProductModel>> productsAsync) {
    final dummyList = List.generate(4, (i) => ProductModel(
      id: i, 
      name: 'اسم لمنتج جديد ومميز', 
      price: 1000, 
      currentPrice: 1000, 
      isOnSale: false,
      stockQuantity: 10, 
      averageRating: 5, 
      reviewsCount: 0, 
      imageUrl: ''
    ));

    return productsAsync.when(
      data: (products) => _buildProductGrid(products, false),
      loading: () => Skeletonizer(child: _buildProductGrid(dummyList, true)),
      error: (e, s) => Center(child: Text('خطأ: $e')),
    );
  }

  Widget _buildProductGrid(List<ProductModel> products, bool isLoading) {
    if (products.isEmpty && !isLoading) {
      return const Center(child: Text('لا توجد منتجات حالياً.'));
    }
    return GridView.builder(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        childAspectRatio: 0.7,
        crossAxisSpacing: 10,
        mainAxisSpacing: 10,
      ),
      itemCount: products.length,
      itemBuilder: (context, index) {
        final p = products[index];
        return GestureDetector(
          onTap: isLoading ? null : () => context.push('/product/${p.id}'),
          child: Card(
            elevation: 2,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Expanded(
                  child: ClipRRect(
                    borderRadius: const BorderRadius.vertical(top: Radius.circular(12)),
                    child: p.imageUrl != null && p.imageUrl!.isNotEmpty
                        ? Image.network(p.imageUrl!, fit: BoxFit.cover)
                        : Container(color: Colors.grey[200], child: const Icon(Icons.image, size: 50)),
                  ),
                ),
                Padding(
                  padding: const EdgeInsets.all(8.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(p.name, maxLines: 2, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.bold)),
                      const SizedBox(height: 4),
                      Text('${p.currentPrice} د.ع', style: const TextStyle(color: Color(0xFF6D0E16), fontWeight: FontWeight.bold)),
                    ],
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}
