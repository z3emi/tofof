import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../providers/wishlist_provider.dart';
import '../../home/providers/store_provider.dart';

class FavoritesScreen extends ConsumerWidget {
  const FavoritesScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final favoriteIds = ref.watch(wishlistProvider).toList()..sort();

    return Scaffold(
      appBar: AppBar(title: const Text('المفضلة')),
      body: favoriteIds.isEmpty
          ? const Center(child: Text('لا توجد منتجات في المفضلة'))
          : ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: favoriteIds.length,
              separatorBuilder: (_, __) => const SizedBox(height: 12),
              itemBuilder: (context, index) {
                final productId = favoriteIds[index];
                final productAsync = ref.watch(productDetailsProvider(productId));

                return productAsync.when(
                  data: (product) => Card(
                    child: ListTile(
                      leading: ClipRRect(
                        borderRadius: BorderRadius.circular(10),
                        child: Image.network(
                          product.imageUrl ?? 'https://placehold.co/80',
                          width: 56,
                          height: 56,
                          fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) => Container(
                            width: 56,
                            height: 56,
                            color: Colors.grey.shade200,
                            child: const Icon(Icons.image),
                          ),
                        ),
                      ),
                      title: Text(product.name),
                      subtitle: Text('${product.currentPrice.toStringAsFixed(0)} د.ع'),
                      trailing: IconButton(
                        icon: const Icon(Icons.favorite, color: Colors.red),
                        onPressed: () async {
                          await ref.read(wishlistProvider.notifier).toggle(productId);
                          if (context.mounted) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(content: Text('تمت الإزالة من المفضلة')),
                            );
                          }
                        },
                      ),
                    ),
                  ),
                  loading: () => const Card(child: ListTile(title: Text('جاري التحميل...'))),
                  error: (_, __) => Card(
                    child: ListTile(
                      title: Text('منتج #$productId'),
                      subtitle: const Text('تعذر تحميل تفاصيل المنتج'),
                    ),
                  ),
                );
              },
            ),
    );
  }
}