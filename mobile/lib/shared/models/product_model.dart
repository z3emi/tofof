class ProductModel {
  final int id;
  final String name;
  final String? description;
  final double price;
  final double? salePrice;
  final double currentPrice;
  final bool isOnSale;
  final int stockQuantity;
  final double averageRating;
  final int reviewsCount;
  final String? imageUrl;
  final List<String> images;
  final List<ProductReview> reviews;
  final List<ProductOptionModel> options;

  ProductModel({
    required this.id,
    required this.name,
    this.description,
    required this.price,
    this.salePrice,
    required this.currentPrice,
    required this.isOnSale,
    required this.stockQuantity,
    this.averageRating = 0.0,
    this.reviewsCount = 0,
    this.imageUrl,
    this.images = const [],
    this.reviews = const [],
    this.options = const [],
  });

  factory ProductModel.fromJson(Map<String, dynamic> json) {
    final galleryRaw =
        json['images'] ?? json['gallery'] ?? json['product_images'];
    final parsedImages = _parseImages(galleryRaw);
    final rawPrimaryImage = json['image_url'] as String?;
    final primaryImage =
        rawPrimaryImage ??
        (parsedImages.isNotEmpty ? parsedImages.first : null);

    final rawReviews =
        json['reviews'] ?? json['comments'] ?? json['product_reviews'];

    return ProductModel(
      id: json['id'] as int,
      name: json['name'] as String? ?? '',
      description: json['description'] as String?,
      price: (json['price'] as num?)?.toDouble() ?? 0.0,
      salePrice: (json['sale_price'] as num?)?.toDouble(),
      currentPrice: (json['current_price'] as num?)?.toDouble() ?? 0.0,
      isOnSale: json['is_on_sale'] as bool? ?? false,
      stockQuantity: json['stock_quantity'] as int? ?? 0,
      averageRating: (json['average_rating'] as num?)?.toDouble() ?? 0.0,
      reviewsCount: json['reviews_count'] as int? ?? 0,
      imageUrl: primaryImage,
      images: parsedImages,
      reviews: _parseReviews(rawReviews),
      options: _parseOptions(json['options'] ?? json['product_options']),
    );
  }

  static List<String> _parseImages(dynamic raw) {
    if (raw is! List) {
      return const [];
    }

    return raw
        .map((item) {
          if (item is String) return item;
          if (item is Map<String, dynamic>) {
            return (item['url'] ?? item['image_url'] ?? item['src'])
                ?.toString();
          }
          return null;
        })
        .whereType<String>()
        .where((url) => url.trim().isNotEmpty)
        .toList();
  }

  static List<ProductReview> _parseReviews(dynamic raw) {
    if (raw is! List) {
      return const [];
    }

    return raw
        .whereType<Map<String, dynamic>>()
        .map(ProductReview.fromJson)
        .toList();
  }

  static List<ProductOptionModel> _parseOptions(dynamic raw) {
    if (raw is! List) {
      return const [];
    }

    return raw
        .whereType<Map<String, dynamic>>()
        .map(ProductOptionModel.fromJson)
        .where((option) => option.values.isNotEmpty)
        .toList();
  }
}

class ProductOptionModel {
  final int id;
  final String name;
  final bool isRequired;
  final List<ProductOptionValueModel> values;

  ProductOptionModel({
    required this.id,
    required this.name,
    required this.isRequired,
    required this.values,
  });

  factory ProductOptionModel.fromJson(Map<String, dynamic> json) {
    final rawValues = json['values'];
    final values = rawValues is List
        ? rawValues
              .whereType<Map<String, dynamic>>()
              .map(ProductOptionValueModel.fromJson)
              .toList()
        : const <ProductOptionValueModel>[];

    return ProductOptionModel(
      id: (json['id'] as num?)?.toInt() ?? 0,
      name: (json['name'] ?? json['name_ar'] ?? json['name_en'] ?? '')
          .toString(),
      isRequired: json['is_required'] == true,
      values: values,
    );
  }
}

class ProductOptionValueModel {
  final int id;
  final String value;

  ProductOptionValueModel({required this.id, required this.value});

  factory ProductOptionValueModel.fromJson(Map<String, dynamic> json) {
    return ProductOptionValueModel(
      id: (json['id'] as num?)?.toInt() ?? 0,
      value: (json['value'] ?? json['value_ar'] ?? json['value_en'] ?? '')
          .toString(),
    );
  }
}

class ProductReview {
  final String author;
  final String comment;
  final double rating;
  final String? createdAt;

  ProductReview({
    required this.author,
    required this.comment,
    required this.rating,
    this.createdAt,
  });

  factory ProductReview.fromJson(Map<String, dynamic> json) {
    return ProductReview(
      author:
          (json['author_name'] ?? json['user_name'] ?? json['name'] ?? 'مستخدم')
              as String,
      comment:
          (json['comment'] ?? json['review'] ?? json['content'] ?? '')
              as String,
      rating: (json['rating'] as num?)?.toDouble() ?? 0.0,
      createdAt: (json['created_at'] ?? json['date'])?.toString(),
    );
  }
}
