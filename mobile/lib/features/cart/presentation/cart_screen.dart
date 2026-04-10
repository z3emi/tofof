import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../providers/cart_provider.dart';

class CartScreen extends ConsumerStatefulWidget {
  const CartScreen({super.key});

  @override
  ConsumerState<CartScreen> createState() => _CartScreenState();
}

class _CartScreenState extends ConsumerState<CartScreen> {
  final _couponCtrl = TextEditingController();

  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(cartProvider.notifier).fetchCart());
  }

  @override
  void dispose() {
    _couponCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final cartState = ref.watch(cartProvider);

    if (cartState.isLoading && cartState.items.isEmpty) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }

    if (cartState.items.isEmpty) {
      return const Scaffold(
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.remove_shopping_cart, size: 80, color: Colors.grey),
              SizedBox(height: 16),
              Text('سلة المشتريات فارغة', style: TextStyle(fontSize: 18)),
            ],
          ),
        ),
      );
    }

    return Scaffold(
      body: Column(
        children: [
          Expanded(
            child: ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: cartState.items.length,
              separatorBuilder: (_, __) => const Divider(),
              itemBuilder: (context, index) {
                final item = cartState.items[index];
                return Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    ClipRRect(
                      borderRadius: BorderRadius.circular(8),
                      child: Image.network(
                        item.imageUrl.isNotEmpty ? item.imageUrl : 'https://placehold.co/100',
                        width: 80, height: 80, fit: BoxFit.cover,
                        errorBuilder: (c,e,s) => Container(width: 80, height: 80, color: Colors.grey[300], child: const Icon(Icons.image)),
                      ),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(item.name, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                          const SizedBox(height: 8),
                          Text('${item.price} د.ع', style: const TextStyle(color: Color(0xFF6D0E16))),
                          const SizedBox(height: 8),
                          Row(
                            children: [
                              IconButton(
                                constraints: const BoxConstraints(),
                                padding: EdgeInsets.zero,
                                icon: const Icon(Icons.remove_circle_outline),
                                onPressed: item.quantity > 1 ? () => ref.read(cartProvider.notifier).updateQuantity(item.selectionKey, item.quantity - 1) : null,
                              ),
                              const SizedBox(width: 8),
                              Text('${item.quantity}', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                              const SizedBox(width: 8),
                              IconButton(
                                constraints: const BoxConstraints(),
                                padding: EdgeInsets.zero,
                                icon: const Icon(Icons.add_circle_outline),
                                onPressed: () => ref.read(cartProvider.notifier).updateQuantity(item.selectionKey, item.quantity + 1),
                              ),
                              const Spacer(),
                              IconButton(
                                icon: const Icon(Icons.delete_outline, color: Colors.red),
                                onPressed: () => ref.read(cartProvider.notifier).removeItem(item.selectionKey),
                              )
                            ],
                          )
                        ],
                      ),
                    )
                  ],
                );
              },
            ),
          ),
          
          // Coupon Section
          Padding(
            padding: const EdgeInsets.all(16.0),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _couponCtrl,
                    decoration: const InputDecoration(
                      hintText: 'أدخل كود الخصم',
                      border: OutlineInputBorder(),
                      contentPadding: EdgeInsets.symmetric(horizontal: 16),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                ElevatedButton(
                  onPressed: () {
                    if (_couponCtrl.text.isNotEmpty) {
                      ref.read(cartProvider.notifier).applyDiscount(_couponCtrl.text);
                    }
                  },
                  child: const Text('تطبيق'),
                )
              ],
            ),
          ),

          // Totals Section
          Container(
            padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
              boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 10)],
            ),
            child: SafeArea(
              top: false,
              child: Column(
                children: [
                  Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
                    const Text('المجموع الفرعي'), Text('${cartState.subtotal} د.ع'),
                  ]),
                  if (cartState.discount > 0) ...[
                    const SizedBox(height: 8),
                    Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
                      const Text('الخصم', style: TextStyle(color: Colors.green)), 
                      Text('-${cartState.discount} د.ع', style: const TextStyle(color: Colors.green)),
                    ]),
                  ],
                  const Divider(height: 24),
                  Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
                    const Text('الإجمالي الطلي', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)), 
                    Text('${cartState.total} د.ع', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: Color(0xFF6D0E16))),
                  ]),
                  const SizedBox(height: 16),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      onPressed: () {
                         // Navigation to checkout screen
                      },
                      child: const Text('إتمام الطلب'),
                    ),
                  )
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
