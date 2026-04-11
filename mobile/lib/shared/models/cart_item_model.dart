class CartItemModel {
  final String selectionKey;
  final int productId;
  final String name;
  final String imageUrl;
  final double price;
  final int quantity;
  final double total;
  final Map<String, dynamic> selectedOptions;

  CartItemModel({
    required this.selectionKey,
    required this.productId,
    required this.name,
    required this.imageUrl,
    required this.price,
    required this.quantity,
    required this.total,
    this.selectedOptions = const {},
  });

  factory CartItemModel.fromJson(Map<String, dynamic> json) {
    final selectedOptions = json['selected_options'];

    return CartItemModel(
      selectionKey: json['selection_key'] as String? ?? json['product_id'].toString(),
      productId: json['product_id'] as int,
      name: json['name'] as String? ?? 'منتج',
      imageUrl: (json['image'] as String?) ?? (json['image_url'] as String?) ?? '',
      price: (json['price'] as num).toDouble(),
      quantity: json['quantity'] as int,
      total: (json['total'] as num).toDouble(),
      selectedOptions: selectedOptions is Map<String, dynamic>
          ? selectedOptions
          : selectedOptions is List
              ? <String, dynamic>{}
              : {},
    );
  }
}
