import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

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
            final orders =
                (data['orders'] as List?)
                    ?.whereType<Map<String, dynamic>>()
                    .toList() ??
                <Map<String, dynamic>>[];

            if (orders.isEmpty) {
              return ListView(
                physics: const AlwaysScrollableScrollPhysics(),
                children: const [
                  SizedBox(height: 160),
                  Icon(
                    Icons.shopping_bag_outlined,
                    size: 64,
                    color: Colors.grey,
                  ),
                  SizedBox(height: 10),
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
                final order = orders[index];
                final orderId = (order['id'] as num?)?.toInt();
                final createdAt = _formatDate(order['created_at']);
                final status = order['status']?.toString() ?? 'pending';
                final paymentMethod =
                    order['payment_method']?.toString() ?? '-';
                final total = (order['total_amount'] as num?)?.toDouble() ?? 0;
                final itemsCount = (order['items_count'] as num?)?.toInt() ?? 0;

                return InkWell(
                  borderRadius: BorderRadius.circular(18),
                  onTap: orderId == null
                      ? null
                      : () => _showOrderDetails(orderId),
                  child: Container(
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(18),
                      border: Border.all(color: const Color(0xFFEADFE1)),
                      boxShadow: [
                        BoxShadow(
                          color: const Color(
                            0xFF6D0E16,
                          ).withValues(alpha: 0.05),
                          blurRadius: 16,
                          offset: const Offset(0, 8),
                        ),
                      ],
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Expanded(
                              child: Text(
                                'طلب #${order['id']}',
                                style: const TextStyle(
                                  fontWeight: FontWeight.w900,
                                  fontSize: 16,
                                ),
                              ),
                            ),
                            _statusChip(status),
                          ],
                        ),
                        const SizedBox(height: 10),
                        Row(
                          children: [
                            _meta('التاريخ', createdAt),
                            const SizedBox(width: 10),
                            _meta('طريقة الدفع', paymentMethod),
                          ],
                        ),
                        const SizedBox(height: 8),
                        Row(
                          children: [
                            _meta('عدد المنتجات', '$itemsCount'),
                            const SizedBox(width: 10),
                            _meta('الإجمالي', _formatPrice(total)),
                          ],
                        ),
                        const SizedBox(height: 10),
                        const Align(
                          alignment: Alignment.centerLeft,
                          child: Text(
                            'عرض التفاصيل',
                            style: TextStyle(
                              color: Color(0xFF6D0E16),
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              },
            );
          },
        ),
      ),
    );
  }

  Future<void> _showOrderDetails(int orderId) async {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => const Center(child: CircularProgressIndicator()),
    );

    try {
      final response = await ref
          .read(authRepositoryProvider)
          .fetchOrderDetails(orderId);
      if (!mounted) return;
      Navigator.of(context).pop();

      final data = response['data'] as Map<String, dynamic>? ?? {};
      final items =
          (data['items'] as List?)
              ?.whereType<Map<String, dynamic>>()
              .toList() ??
          <Map<String, dynamic>>[];

      if (!mounted) return;
      showModalBottomSheet<void>(
        context: context,
        isScrollControlled: true,
        showDragHandle: true,
        builder: (context) {
          return DraggableScrollableSheet(
            expand: false,
            initialChildSize: 0.88,
            maxChildSize: 0.95,
            minChildSize: 0.6,
            builder: (context, controller) {
              return ListView(
                controller: controller,
                padding: const EdgeInsets.all(16),
                children: [
                  Text(
                    'تفاصيل الطلب #${data['id']}',
                    style: const TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Expanded(
                        child: _infoTile(
                          'الحالة',
                          _statusText(data['status']?.toString() ?? 'pending'),
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: _infoTile(
                          'الدفع',
                          data['payment_method']?.toString() ?? '-',
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 10),
                  Row(
                    children: [
                      Expanded(
                        child: _infoTile(
                          'الإجمالي',
                          _formatPrice(
                            (data['total_amount'] as num?)?.toDouble() ?? 0,
                          ),
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: _infoTile(
                          'مدفوع من المحفظة',
                          _formatPrice(
                            (data['wallet_paid'] as num?)?.toDouble() ?? 0,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 10),
                  _infoTile(
                    'العنوان',
                    '${data['governorate'] ?? ''} - ${data['city'] ?? ''}\n${data['address_details'] ?? ''}',
                  ),
                  if ((data['nearest_landmark']?.toString().trim().isNotEmpty ??
                      false)) ...[
                    const SizedBox(height: 10),
                    _infoTile('أقرب نقطة', data['nearest_landmark'].toString()),
                  ],
                  const SizedBox(height: 14),
                  const Text(
                    'المنتجات',
                    style: TextStyle(fontWeight: FontWeight.w900, fontSize: 16),
                  ),
                  const SizedBox(height: 8),
                  ...items.map((item) {
                    final qty = (item['quantity'] as num?)?.toInt() ?? 0;
                    final price = (item['price'] as num?)?.toDouble() ?? 0;
                    final total = (item['total'] as num?)?.toDouble() ?? 0;
                    final image = item['image']?.toString();
                    return Container(
                      margin: const EdgeInsets.only(bottom: 8),
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF9F7F8),
                        borderRadius: BorderRadius.circular(14),
                      ),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          ClipRRect(
                            borderRadius: BorderRadius.circular(10),
                            child: Image.network(
                              (image != null && image.isNotEmpty)
                                  ? image
                                  : 'https://placehold.co/80x80',
                              width: 70,
                              height: 70,
                              fit: BoxFit.cover,
                              errorBuilder: (_, __, ___) => Container(
                                width: 70,
                                height: 70,
                                color: Colors.grey.shade200,
                                child: const Icon(Icons.image_outlined),
                              ),
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  item['product_name']?.toString() ?? 'منتج',
                                  style: const TextStyle(
                                    fontWeight: FontWeight.w800,
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  'الكمية: $qty',
                                  style: const TextStyle(fontSize: 13),
                                ),
                                Text(
                                  'سعر القطعة: ${_formatPrice(price)}',
                                  style: const TextStyle(fontSize: 13),
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  'الإجمالي: ${_formatPrice(total)}',
                                  style: const TextStyle(
                                    fontWeight: FontWeight.w700,
                                    color: Color(0xFF6D0E16),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
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
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text(e.toString())));
    }
  }

  Widget _statusChip(String status) {
    final map = _statusStyle(status);
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: (map['color'] as Color).withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(
        map['text'] as String,
        style: TextStyle(
          color: map['color'] as Color,
          fontWeight: FontWeight.w700,
        ),
      ),
    );
  }

  Widget _meta(String title, String value) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.all(10),
        decoration: BoxDecoration(
          color: const Color(0xFFF9F4F5),
          borderRadius: BorderRadius.circular(12),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              title,
              style: TextStyle(color: Colors.grey.shade700, fontSize: 12),
            ),
            const SizedBox(height: 2),
            Text(
              value,
              style: const TextStyle(fontWeight: FontWeight.w700),
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ),
      ),
    );
  }

  Widget _infoTile(String title, String value) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: const Color(0xFFF7F3F4),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            title,
            style: TextStyle(color: Colors.grey.shade700, fontSize: 12),
          ),
          const SizedBox(height: 4),
          Text(value, style: const TextStyle(fontWeight: FontWeight.w700)),
        ],
      ),
    );
  }

  String _formatDate(dynamic raw) {
    if (raw == null) return '-';
    final parsed = DateTime.tryParse(raw.toString());
    if (parsed == null) return raw.toString();
    return DateFormat('yyyy/MM/dd - HH:mm').format(parsed);
  }

  String _formatPrice(num value) {
    return '${NumberFormat('#,##0.00').format(value)} د.ع';
  }

  Map<String, Object> _statusStyle(String status) {
    switch (status) {
      case 'processing':
        return {'text': 'قيد التجهيز', 'color': const Color(0xFF005BBB)};
      case 'shipped':
        return {'text': 'تم الشحن', 'color': const Color(0xFF7A4A00)};
      case 'delivered':
        return {'text': 'تم التسليم', 'color': const Color(0xFF1A7F37)};
      case 'cancelled':
        return {'text': 'ملغي', 'color': const Color(0xFFC62828)};
      case 'returned':
        return {'text': 'مرتجع', 'color': const Color(0xFF6A1B9A)};
      default:
        return {'text': 'قيد الانتظار', 'color': const Color(0xFF6D0E16)};
    }
  }

  String _statusText(String status) => _statusStyle(status)['text'] as String;
}
