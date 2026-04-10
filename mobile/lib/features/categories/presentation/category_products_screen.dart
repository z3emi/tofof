import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../home/providers/store_provider.dart';
import '../../../shared/models/product_model.dart';
import 'package:go_router/go_router.dart';

class CategoryProductsScreen extends ConsumerWidget {
  final int categoryId;
  const CategoryProductsScreen({super.key, required this.categoryId});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    // Ideally use a filtered provider like categoryProductsProvider(categoryId)
    // Here we use the home products as a placeholder until filtering is implemented
    final productsAsync = ref.watch(homeProductsProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('المنتجات')),
      body: productsAsync.when(
        data: (products) => GridView.builder(
          padding: const EdgeInsets.all(16),
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
              onTap: () => context.push('/product/${p.id}'),
              child: Card(
                elevation: 2,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Expanded(
                      child: ClipRRect(
                        borderRadius: const BorderRadius.vertical(top: Radius.circular(12)),
                        child: p.imageUrl != null
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
        ),
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, s) => Center(child: Text('خطأ: $e')),
      ),
    );
  }
}
