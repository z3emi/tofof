import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_constants.dart';
import '../../../core/network/dio_client.dart';
import '../../../shared/models/product_model.dart';
import '../../../shared/models/category_model.dart';

final storeRepositoryProvider = Provider<StoreRepository>((ref) {
  return StoreRepository(ref.watch(dioProvider));
});

class StoreRepository {
  final Dio _dio;

  StoreRepository(this._dio);

  Future<Map<String, dynamic>> fetchSliders() async {
    final response = await _dio.get(ApiConstants.sliders);
    return response.data['data'];
  }

  Future<List<CategoryModel>> fetchSections() async {
    final response = await _dio.get(ApiConstants.sections);
    final data = response.data['data'] as List;
    return data.map((e) => CategoryModel.fromJson(e)).toList();
  }

  Future<List<CategoryModel>> fetchCategories() async {
    final response = await _dio.get(ApiConstants.categories);
    final data = response.data['data'] as List;
    return data.map((e) => CategoryModel.fromJson(e)).toList();
  }

  Future<Map<String, dynamic>> fetchProducts({
    int page = 1,
    int perPage = 20,
    int? categoryId,
    int? sectionId,
    String? sort,
    String? query,
  }) async {
    final Map<String, dynamic> queryParams = {
      'page': page,
      'per_page': perPage,
    };
    if (categoryId != null) queryParams['category_id'] = categoryId;
    if (sectionId != null) queryParams['section_id'] = sectionId;
    if (sort != null) queryParams['sort'] = sort;
    if (query != null && query.isNotEmpty) queryParams['q'] = query;

    final response = await _dio.get(
      ApiConstants.products,
      queryParameters: queryParams,
    );

    final items = (response.data['data'] as List)
        .map((e) => ProductModel.fromJson(e))
        .toList();
    final meta = response.data['meta'];

    return {'items': items, 'meta': meta};
  }

  Future<ProductModel> fetchProduct(int id) async {
    final response = await _dio.get('${ApiConstants.products}/$id');
    return ProductModel.fromJson(response.data['data']);
  }
}
