class CategoryModel {
  final int id;
  final String name;
  final String? slug;
  final String? imageUrl;
  final int productsCount;
  final List<CategoryModel> children;

  CategoryModel({
    required this.id,
    required this.name,
    this.slug,
    this.imageUrl,
    this.productsCount = 0,
    this.children = const [],
  });

  factory CategoryModel.fromJson(Map<String, dynamic> json) {
    return CategoryModel(
      id: json['id'] as int,
      name: json['name'] as String? ?? '',
      slug: json['slug'] as String?,
      imageUrl: json['image_url'] as String?,
      productsCount: json['products_count'] as int? ?? 0,
      children:
          (json['children'] as List<dynamic>?)
              ?.map((e) => CategoryModel.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
    );
  }
}
