class ReviewModel {
  final int id;
  final String? userName;
  final String? userAvatar;
  final double rating;
  final String? comment;
  final String? createdAt;

  ReviewModel({
    required this.id,
    this.userName,
    this.userAvatar,
    required this.rating,
    this.comment,
    this.createdAt,
  });

  factory ReviewModel.fromJson(Map<String, dynamic> json) {
    final user = json['user'] as Map<String, dynamic>?;
    return ReviewModel(
      id: json['id'] as int? ?? 0,
      userName: user?['name'] as String?,
      userAvatar: user?['avatar'] as String?,
      rating: (json['rating'] as num?)?.toDouble() ?? 0.0,
      comment: json['comment'] as String?,
      createdAt: json['created_at'] as String?,
    );
  }
}

class ProductImageModel {
  final int id;
  final String url;
  final bool isPrimary;

  ProductImageModel({required this.id, required this.url, required this.isPrimary});

  factory ProductImageModel.fromJson(Map<String, dynamic> json) {
    return ProductImageModel(
      id: json['id'] as int? ?? 0,
      url: json['url'] as String? ?? '',
      isPrimary: json['is_primary'] as bool? ?? false,
    );
  }
}

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
  final String? sku;
  final Map<String, dynamic>? category;
  final List<ProductImageModel> images;
  final List<ReviewModel> reviews;

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
    this.sku,
    this.category,
    this.images = const [],
    this.reviews = const [],
  });

  factory ProductModel.fromJson(Map<String, dynamic> json) {
    final imagesList = (json['images'] as List<dynamic>?)
            ?.map((e) => ProductImageModel.fromJson(e as Map<String, dynamic>))
            .toList() ??
        [];
    final reviewsList = (json['reviews'] as List<dynamic>?)
            ?.map((e) => ReviewModel.fromJson(e as Map<String, dynamic>))
            .toList() ??
        [];

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
      imageUrl: json['image_url'] as String?,
      sku: json['sku'] as String?,
      category: json['category'] as Map<String, dynamic>?,
      images: imagesList,
      reviews: reviewsList,
    );
  }
}
