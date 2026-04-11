import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

import '../../auth/data/auth_repository.dart';
import '../providers/wishlist_provider.dart';

class FavoritesScreen extends ConsumerStatefulWidget {
  const FavoritesScreen({super.key});

  @override
  ConsumerState<FavoritesScreen> createState() => _FavoritesScreenState();
}

class _FavoritesScreenState extends ConsumerState<FavoritesScreen> {
  late Future<Map<String, dynamic>> _future;

  @override
  void initState() {
    super.initState();
    _future = ref.read(authRepositoryProvider).fetchFavorites();
  }

  Future<void> _refresh() async {
    await ref.read(wishlistProvider.notifier).reload();
    setState(() {
      _future = ref.read(authRepositoryProvider).fetchFavorites();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('المفضلة')),
      body: RefreshIndicator(
        onRefresh: _refresh,
        child: FutureBuilder<Map<String, dynamic>>(
          future: _future,
          builder: (context, snapshot) {
            if (snapshot.connectionState == ConnectionState.waiting) {
              return const Center(child: CircularProgressIndicator());
            }

            if (snapshot.hasError) {
              return ListView(
                physics: const AlwaysScrollableScrollPhysics(),
                children: [
                  const SizedBox(height: 160),
                  Center(child: Text(snapshot.error.toString())),
                ],
              );
            }

            final data =
                snapshot.data?['data'] as Map<String, dynamic>? ??
                <String, dynamic>{};
            final items =
                (data['items'] as List?)
                    ?.whereType<Map<String, dynamic>>()
                    .toList() ??
                <Map<String, dynamic>>[];

            if (items.isEmpty) {
              return ListView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(20),
                children: const [
                  SizedBox(height: 120),
                  Icon(Icons.favorite_border, size: 60, color: Colors.grey),
                  SizedBox(height: 12),
                  Center(child: Text('المفضلة فارغة حاليًا')),
                  SizedBox(height: 6),
                  Center(child: Text('أضف منتجاتك المفضلة لتظهر هنا')),
                ],
              );
            }

            return ListView.separated(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.all(16),
              itemCount: items.length,
              separatorBuilder: (_, __) => const SizedBox(height: 12),
              itemBuilder: (context, index) {
                final item = items[index];
                final productId = (item['id'] as num?)?.toInt();
                final image = item['image']?.toString();
                final name = item['name']?.toString() ?? 'منتج';
                final currentPrice =
                    (item['current_price'] as num?)?.toDouble() ?? 0;
                final salePrice = (item['sale_price'] as num?)?.toDouble();
                final price =
                    (item['price'] as num?)?.toDouble() ?? currentPrice;
                final onSale = item['is_on_sale'] == true;

                return InkWell(
                  borderRadius: BorderRadius.circular(18),
                  onTap: productId != null
                      ? () => context.push('/product/$productId')
                      : null,
                  child: Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(18),
                      border: Border.all(color: const Color(0xFFE9D8DB)),
                      boxShadow: [
                        BoxShadow(
                          color: const Color(
                            0xFF6D0E16,
                          ).withValues(alpha: 0.06),
                          blurRadius: 14,
                          offset: const Offset(0, 8),
                        ),
                      ],
                    ),
                    child: Row(
                      children: [
                        ClipRRect(
                          borderRadius: BorderRadius.circular(12),
                          child: Image.network(
                            (image != null && image.isNotEmpty)
                                ? image
                                : 'https://placehold.co/120x120',
                            width: 82,
                            height: 82,
                            fit: BoxFit.cover,
                            errorBuilder: (_, __, ___) => Container(
                              width: 82,
                              height: 82,
                              color: Colors.grey.shade200,
                              child: const Icon(Icons.image_outlined),
                            ),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                name,
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                                style: const TextStyle(
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                              const SizedBox(height: 8),
                              Text(
                                _formatPrice(currentPrice),
                                style: const TextStyle(
                                  color: Color(0xFF6D0E16),
                                  fontWeight: FontWeight.w800,
                                ),
                              ),
                              if (onSale) ...[
                                const SizedBox(height: 2),
                                Text(
                                  _formatPrice(salePrice ?? price),
                                  style: const TextStyle(
                                    decoration: TextDecoration.lineThrough,
                                    color: Colors.grey,
                                  ),
                                ),
                              ],
                            ],
                          ),
                        ),
                        IconButton(
                          icon: const Icon(Icons.favorite, color: Colors.red),
                          onPressed: productId == null
                              ? null
                              : () async {
                                  try {
                                    await ref
                                        .read(wishlistProvider.notifier)
                                        .toggle(productId);
                                    await _refresh();
                                  } catch (e) {
                                    if (!mounted) return;
                                    ScaffoldMessenger.of(
                                      this.context,
                                    ).showSnackBar(
                                      SnackBar(content: Text(e.toString())),
                                    );
                                  }
                                },
                        ),
                      ],
                    ),
                  ),
                );
              },
            );
          },
        ),
      ),
    );
  }

  String _formatPrice(num value) {
    return '${NumberFormat('#,##0.00').format(value)} د.ع';
  }
}
