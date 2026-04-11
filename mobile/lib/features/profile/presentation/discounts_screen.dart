import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../../auth/data/auth_repository.dart';

class DiscountsScreen extends ConsumerStatefulWidget {
  const DiscountsScreen({super.key});

  @override
  ConsumerState<DiscountsScreen> createState() => _DiscountsScreenState();
}

class _DiscountsScreenState extends ConsumerState<DiscountsScreen> {
  late Future<Map<String, dynamic>> _future;

  @override
  void initState() {
    super.initState();
    _future = ref.read(authRepositoryProvider).fetchDiscounts();
  }

  Future<void> _refresh() async {
    setState(() {
      _future = ref.read(authRepositoryProvider).fetchDiscounts();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('أكواد الخصم')),
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
                physics: const AlwaysScrollableScrollPhysics(),
                children: [
                  const SizedBox(height: 160),
                  Center(child: Text(snapshot.error.toString())),
                ],
              );
            }

            final data = snapshot.data?['data'] as Map<String, dynamic>? ?? {};
            final discounts = (data['discounts'] as List?)?.whereType<Map<String, dynamic>>().toList() ?? <Map<String, dynamic>>[];

            if (discounts.isEmpty) {
              return ListView(
                physics: const AlwaysScrollableScrollPhysics(),
                children: const [
                  SizedBox(height: 160),
                  Icon(Icons.discount_outlined, size: 64, color: Colors.grey),
                  SizedBox(height: 8),
                  Center(child: Text('لا توجد أكواد خصم متاحة الآن')),
                ],
              );
            }

            return ListView.separated(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.all(16),
              itemCount: discounts.length,
              separatorBuilder: (_, __) => const SizedBox(height: 12),
              itemBuilder: (context, index) {
                final discount = discounts[index];
                final code = discount['code']?.toString() ?? '';
                final type = discount['type']?.toString() ?? 'fixed';
                final value = (discount['value'] as num?)?.toDouble() ?? 0;
                final maxDiscount = (discount['max_discount_amount'] as num?)?.toDouble();
                final scope = discount['scope']?.toString() ?? 'all';
                final expiresAtRaw = discount['expires_at'];
                final expiresAt = expiresAtRaw != null ? DateTime.tryParse(expiresAtRaw.toString()) : null;
                final products = (discount['products'] as List?)?.whereType<Map<String, dynamic>>().toList() ?? <Map<String, dynamic>>[];
                final categories = (discount['categories'] as List?)?.whereType<Map<String, dynamic>>().toList() ?? <Map<String, dynamic>>[];

                return Container(
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(20),
                    gradient: const LinearGradient(
                      colors: [Color(0xFFFDF3E0), Color(0xFFF7E4C0)],
                      begin: Alignment.topRight,
                      end: Alignment.bottomLeft,
                    ),
                    border: Border.all(color: const Color(0xFFE7CC90)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Expanded(
                            child: Text(code, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900, letterSpacing: 1.1)),
                          ),
                          FilledButton.icon(
                            onPressed: code.isEmpty
                                ? null
                                : () async {
                                    await Clipboard.setData(ClipboardData(text: code));
                                    if (!context.mounted) return;
                                    ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('تم نسخ كود الخصم')));
                                  },
                            icon: const Icon(Icons.copy_outlined, size: 18),
                            label: const Text('نسخ'),
                            style: FilledButton.styleFrom(backgroundColor: const Color(0xFF6D0E16)),
                          ),
                        ],
                      ),
                      const SizedBox(height: 10),
                      Wrap(
                        spacing: 8,
                        runSpacing: 8,
                        children: [
                          _chip(_typeLabel(type, value), const Color(0xFF6D0E16)),
                          _chip(_scopeLabel(scope), const Color(0xFF8B5E00)),
                          _chip(expiresAt == null ? 'بدون تاريخ انتهاء' : 'ينتهي: ${DateFormat('yyyy/MM/dd').format(expiresAt)}', const Color(0xFF1A5D4A)),
                          if (maxDiscount != null) _chip('حد أعلى: ${maxDiscount.toStringAsFixed(2)} د.ع', const Color(0xFF274690)),
                        ],
                      ),
                      const SizedBox(height: 10),
                      if (products.isNotEmpty) ...[
                        const Text('ينطبق على منتجات محددة:', style: TextStyle(fontWeight: FontWeight.w700)),
                        const SizedBox(height: 4),
                        Text(products.map((e) => e['name']?.toString() ?? '').where((e) => e.isNotEmpty).take(3).join('، ')),
                        if (products.length > 3) Text('و ${products.length - 3} منتجات أخرى', style: TextStyle(color: Colors.grey.shade700)),
                      ],
                      if (categories.isNotEmpty) ...[
                        const SizedBox(height: 8),
                        const Text('ينطبق على فئات محددة:', style: TextStyle(fontWeight: FontWeight.w700)),
                        const SizedBox(height: 4),
                        Text(categories.map((e) => e['name']?.toString() ?? '').where((e) => e.isNotEmpty).take(3).join('، ')),
                        if (categories.length > 3) Text('و ${categories.length - 3} فئات أخرى', style: TextStyle(color: Colors.grey.shade700)),
                      ],
                      if ((discount['description']?.toString().trim().isNotEmpty ?? false)) ...[
                        const SizedBox(height: 8),
                        Text(discount['description'].toString(), style: TextStyle(color: Colors.grey.shade800)),
                      ],
                    ],
                  ),
                );
              },
            );
          },
        ),
      ),
    );
  }

  String _typeLabel(String type, double value) {
    if (type == 'percentage') return 'خصم نسبة ${value.toStringAsFixed(2)}%';
    if (type == 'free_shipping') return 'شحن مجاني';
    return 'خصم ثابت ${value.toStringAsFixed(2)} د.ع';
  }

  String _scopeLabel(String scope) {
    if (scope == 'products') return 'لمنتجات محددة';
    if (scope == 'categories') return 'لفئات محددة';
    return 'عام على كل المنتجات';
  }

  Widget _chip(String label, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(label, style: TextStyle(color: color, fontWeight: FontWeight.w700, fontSize: 12)),
    );
  }
}
