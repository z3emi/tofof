import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../auth/data/auth_repository.dart';

class OrdersScreen extends ConsumerStatefulWidget {
  const OrdersScreen({super.key});

  @override
  ConsumerState<OrdersScreen> createState() => _OrdersScreenState();
}

class _OrdersScreenState extends ConsumerState<OrdersScreen> {
  late Future<Map<String, dynamic>> _future;

  @override
  void initState() {
    super.initState();
    _future = ref.read(authRepositoryProvider).fetchOrders();
  }

  Future<void> _refresh() async {
    setState(() {
      _future = ref.read(authRepositoryProvider).fetchOrders();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('طلباتي')),
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
                children: [
                  const SizedBox(height: 160),
                  Center(child: Text(snapshot.error.toString())),
                ],
              );
            }

            final data = snapshot.data?['data'] as Map<String, dynamic>? ?? {};
            final orders = (data['orders'] as List?) ?? const [];

            if (orders.isEmpty) {
              return ListView(
                physics: const AlwaysScrollableScrollPhysics(),
                children: const [
                  SizedBox(height: 160),
                  Center(child: Text('لا توجد طلبات حتى الآن')),
                ],
              );
            }

            return ListView.separated(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.all(16),
              itemCount: orders.length,
              separatorBuilder: (_, __) => const SizedBox(height: 12),
              itemBuilder: (context, index) {
                final order = orders[index] as Map<String, dynamic>;
                final createdAt = order['created_at']?.toString() ?? '';
                return Card(
                  child: ListTile(
                    leading: CircleAvatar(
                      backgroundColor: const Color(0xFF6D0E16).withValues(alpha: 0.12),
                      child: Text('#${order['id']}', style: const TextStyle(color: Color(0xFF6D0E16))),
                    ),
                    title: Text('طلب #${order['id']}'),
                    subtitle: Text('الحالة: ${order['status'] ?? 'غير محدد'}\n$createdAt'),
                    isThreeLine: true,
                    trailing: Text('${order['total_amount']} د.ع', style: const TextStyle(fontWeight: FontWeight.w700)),
                    onTap: () => _showOrderDetails(order['id'] as num),
                  ),
                );
              },
            );
          },
        ),
      ),
    );
  }

  Future<void> _showOrderDetails(num orderId) async {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (dialogContext) {
        return const Center(child: CircularProgressIndicator());
      },
    );

    try {
      final response = await ref.read(authRepositoryProvider).fetchOrderDetails(orderId.toInt());
      if (!mounted) return;
      Navigator.of(context).pop();

      final data = response['data'] as Map<String, dynamic>? ?? {};
      final items = (data['items'] as List?) ?? const [];

      if (!mounted) return;
      showModalBottomSheet<void>(
        context: context,
        showDragHandle: true,
        isScrollControlled: true,
        builder: (context) {
          return DraggableScrollableSheet(
            expand: false,
            initialChildSize: 0.8,
            minChildSize: 0.5,
            maxChildSize: 0.95,
            builder: (context, controller) {
              return ListView(
                controller: controller,
                padding: const EdgeInsets.all(16),
                children: [
                  Text('طلب #${data['id']}', style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w800)),
                  const SizedBox(height: 12),
                  Text('الحالة: ${data['status'] ?? 'غير محدد'}'),
                  Text('طريقة الدفع: ${data['payment_method'] ?? 'غير محددة'}'),
                  Text('الخصم: ${data['discount_amount'] ?? 0} د.ع'),
                  Text('التوصيل: ${data['shipping_cost'] ?? 0} د.ع'),
                  const SizedBox(height: 16),
                  const Text('المنتجات', style: TextStyle(fontWeight: FontWeight.w700)),
                  const SizedBox(height: 8),
                  ...items.map((item) {
                    final map = item as Map<String, dynamic>;
                    return ListTile(
                      contentPadding: EdgeInsets.zero,
                      title: Text(map['product_name']?.toString() ?? 'منتج'),
                      subtitle: Text('الكمية: ${map['quantity']}'),
                      trailing: Text('${map['total']} د.ع'),
                    );
                  }),
                ],
              );
            },
          );
        },
      );
    } catch (e) {
      if (!mounted) return;
      Navigator.of(context).pop();
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    }
  }
}