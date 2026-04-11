import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:skeletonizer/skeletonizer.dart';

import '../providers/store_provider.dart';

class HomeView extends ConsumerWidget {
  final bool showAppBar;

  const HomeView({super.key, this.showAppBar = true});

  const HomeView.embedded({super.key, this.showAppBar = false});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final slidersAsync = ref.watch(slidersProvider);
    final productsAsync = ref.watch(homeProductsProvider);

    final body = RefreshIndicator(
      onRefresh: () async {
        ref.invalidate(slidersProvider);
        ref.invalidate(homeProductsProvider);
      },
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            slidersAsync.when(
              data: (data) => _buildSliders(data['hero'] ?? []),
              loading: () => Skeletonizer(
                enabled: true,
                child: _buildSliders(const [
                  {'image_url': null}
                ]),
              ),
              error: (e, s) => const SizedBox(height: 200, child: Center(child: Text('خطأ في جلب العروض'))),
            ),

            const Padding(
              padding: EdgeInsets.all(16.0),
              child: Text('وصل حديثاً', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
            ),

            productsAsync.when(
              data: (products) => _buildProductsGrid(products),
              loading: () => Skeletonizer(
                enabled: true,
                child: _buildProductsGrid(List.generate(6, (index) => _HomeDummyProduct(index))),
              ),
              error: (e, s) => Center(child: Text('خطأ: $e')),
            ),
            const SizedBox(height: 110),
          ],
        ),
      ),
    );

    if (!showAppBar) {
      return body;
    }

    return Scaffold(
      appBar: AppBar(
        title: Image.asset('assets/images/logo.png', height: 35),
        actions: [
          IconButton(onPressed: () {}, icon: const Icon(Icons.search)),
          IconButton(onPressed: () {}, icon: const Icon(Icons.notifications_none)),
        ],
      ),
      body: body,
    );
  }

  Widget _buildProductsGrid(List<dynamic> products) {
    return GridView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      padding: const EdgeInsets.symmetric(horizontal: 16),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        childAspectRatio: 0.65,
        crossAxisSpacing: 10,
        mainAxisSpacing: 10,
      ),
      itemCount: products.length,
      itemBuilder: (context, index) {
        final p = products[index];
        final imageUrl = p is _HomeDummyProduct ? p.imageUrl : p.imageUrl as String?;
        final name = p is _HomeDummyProduct ? p.name : p.name as String;
        final price = p is _HomeDummyProduct ? p.currentPrice : p.currentPrice as double;
        final id = p is _HomeDummyProduct ? p.id : p.id as int;

        return GestureDetector(
          onTap: p is _HomeDummyProduct ? null : () => context.push('/product/$id'),
          child: Card(
            elevation: 2,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Expanded(
                  child: ClipRRect(
                    borderRadius: const BorderRadius.vertical(top: Radius.circular(12)),
                    child: imageUrl != null
                        ? Image.network(imageUrl, fit: BoxFit.cover)
                        : Container(color: Colors.grey[200], child: const Icon(Icons.image, size: 50)),
                  ),
                ),
                Padding(
                  padding: const EdgeInsets.all(8.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(name, maxLines: 2, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.bold)),
                      const SizedBox(height: 4),
                      Text('${price.toStringAsFixed(0)} د.ع', style: const TextStyle(color: Color(0xFF6D0E16), fontWeight: FontWeight.bold)),
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

  Widget _buildSliders(List dynamicSlides) {
    if (dynamicSlides.isEmpty) return const SizedBox();
    return AspectRatio(
      aspectRatio: 2.2, // Matches typical wide store sliders (adjust if needed to 16:9 or 3:1)
      child: PageView.builder(
        itemCount: dynamicSlides.length,
        itemBuilder: (context, index) {
          final slide = dynamicSlides[index];
          return Container(
            margin: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(12),
              image: slide['image_url'] != null
                  ? DecorationImage(image: NetworkImage(slide['image_url']), fit: BoxFit.cover)
                  : null,
              color: Colors.grey[300],
            ),
          );
        },
      ),
    );
  }
}

class _HomeDummyProduct {
  final int id;
  final String name;
  final double currentPrice;
  final String? imageUrl;

  _HomeDummyProduct(this.id)
      : name = 'منتج تجريبي أنيق',
        currentPrice = 25000,
        imageUrl = null;
}
