import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../shared/models/product_model.dart';
import '../../../shared/models/category_model.dart';
import '../data/store_repository.dart';

final slidersProvider = FutureProvider<Map<String, dynamic>>((ref) async {
  return ref.watch(storeRepositoryProvider).fetchSliders();
});

final sectionsProvider = FutureProvider<List<CategoryModel>>((ref) async {
  return ref.watch(storeRepositoryProvider).fetchSections();
});

final categoriesProvider = FutureProvider<List<CategoryModel>>((ref) async {
  return ref.watch(storeRepositoryProvider).fetchCategories();
});

final homeProductsProvider = FutureProvider<List<ProductModel>>((ref) async {
  // Fetch latest products for the homepage
  final result = await ref
      .watch(storeRepositoryProvider)
      .fetchProducts(perPage: 10);
  return result['items'] as List<ProductModel>;
});

final storeProductsProvider = FutureProvider<List<ProductModel>>((ref) async {
  final result = await ref
      .watch(storeRepositoryProvider)
      .fetchProducts(perPage: 24, sort: 'latest');
  return result['items'] as List<ProductModel>;
});

final productDetailsProvider = FutureProvider.family<ProductModel, int>((
  ref,
  id,
) async {
  return ref.watch(storeRepositoryProvider).fetchProduct(id);
});
