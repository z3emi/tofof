class CategoryModel {
  final int id;
  final String name;
  final String? nameAr;
  final String? nameEn;
  final String? slug;
  final String? imageUrl;
  final int productsCount;
  final List<CategoryModel> children;

  CategoryModel({
    required this.id,
    required this.name,
    this.nameAr,
    this.nameEn,
    this.slug,
    this.imageUrl,
    this.productsCount = 0,
    this.children = const [],
  });

  String localizedName(bool isArabic) {
    final fallback = name.trim();
    if (isArabic) {
      final ar = nameAr?.trim();
      if (ar != null && ar.isNotEmpty) return ar;
      final en = nameEn?.trim();
      if (en != null && en.isNotEmpty && fallback.isEmpty) return en;
      return fallback.isNotEmpty ? fallback : (en ?? '');
    }

    final en = nameEn?.trim();
    if (en != null && en.isNotEmpty) return en;
    final ar = nameAr?.trim();
    if (ar != null && ar.isNotEmpty && fallback.isEmpty) return ar;
    return fallback.isNotEmpty ? fallback : (ar ?? '');
  }

  factory CategoryModel.fromJson(Map<String, dynamic> json) {
    return CategoryModel(
      id: json['id'] as int,
      name: (json['name'] ?? json['name_ar'] ?? json['name_en'] ?? '')
          .toString(),
      nameAr: json['name_ar']?.toString(),
      nameEn: json['name_en']?.toString(),
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
