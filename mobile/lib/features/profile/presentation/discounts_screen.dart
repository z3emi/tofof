import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

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
            final discounts = (data['discounts'] as List?) ?? const [];

            if (discounts.isEmpty) {
              return ListView(
                physics: const AlwaysScrollableScrollPhysics(),
                children: const [
                  SizedBox(height: 160),
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
                final discount = discounts[index] as Map<String, dynamic>;
                final code = discount['code']?.toString() ?? '';
                final description = discount['description']?.toString() ?? 'كود خصم متاح للحساب';

                return Card(
                  child: ListTile(
                    leading: const CircleAvatar(
                      backgroundColor: Color(0xFF6D0E16),
                      child: Icon(Icons.discount_outlined, color: Colors.white),
                    ),
                    title: Text(code, style: const TextStyle(fontWeight: FontWeight.w800)),
                    subtitle: Text(description),
                    trailing: IconButton(
                      icon: const Icon(Icons.shopping_cart_checkout_outlined),
                      onPressed: () => _showSnack(context, 'استخدم الكود داخل السلة: $code'),
                    ),
                    onTap: () => _showSnack(context, 'انسخ الكود واستخدمه في السلة: $code'),
                  ),
                );
              },
            );
          },
        ),
      ),
    );
  }

  void _showSnack(BuildContext context, String message) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message)));
  }
}