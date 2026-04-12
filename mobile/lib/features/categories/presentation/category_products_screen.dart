import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../../home/providers/store_provider.dart';
import 'package:go_router/go_router.dart';

class CategoryProductsScreen extends ConsumerWidget {
  final int categoryId;
  const CategoryProductsScreen({super.key, required this.categoryId});

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
  Widget build(BuildContext context, WidgetRef ref) {
    final isArabic = Localizations.localeOf(context).languageCode == 'ar';
    final productsAsync = ref.watch(homeProductsProvider);

    return Scaffold(
      appBar: AppBar(title: Text(_tr(isArabic, 'المنتجات', 'Products'))),
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
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Expanded(
                      child: ClipRRect(
                        borderRadius: const BorderRadius.vertical(
                          top: Radius.circular(12),
                        ),
                        child: p.imageUrl != null
                            ? Image.network(p.imageUrl!, fit: BoxFit.cover)
                            : Container(
                                color: Colors.grey[200],
                                child: const Icon(Icons.image, size: 50),
                              ),
                      ),
                    ),
                    Padding(
                      padding: const EdgeInsets.all(8.0),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            p.localizedName(isArabic),
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(fontWeight: FontWeight.bold),
                          ),
                          const SizedBox(height: 4),
                          if (p.isOnSale)
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  '${_formatPrice(p.price)} ${_tr(isArabic, 'د.ع', 'IQD')}',
                                  style: const TextStyle(
                                    color: Colors.grey,
                                    fontSize: 12,
                                    decoration: TextDecoration.lineThrough,
                                  ),
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  '${_formatPrice(p.currentPrice)} ${_tr(isArabic, 'د.ع', 'IQD')}',
                                  style: const TextStyle(
                                    color: Color(0xFF6D0E16),
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ],
                            )
                          else
                            Text(
                              '${_formatPrice(p.currentPrice)} ${_tr(isArabic, 'د.ع', 'IQD')}',
                              style: const TextStyle(
                                color: Color(0xFF6D0E16),
                                fontWeight: FontWeight.bold,
                              ),
                            ),
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
        error: (e, s) => Center(child: Text('${_tr(Localizations.localeOf(context).languageCode == 'ar', 'خطأ', 'Error')}: $e')),
      ),
    );
  }
}
