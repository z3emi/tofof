import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../auth/data/auth_repository.dart';

class AddressesScreen extends ConsumerStatefulWidget {
  const AddressesScreen({super.key});

  @override
  ConsumerState<AddressesScreen> createState() => _AddressesScreenState();
}

class _AddressesScreenState extends ConsumerState<AddressesScreen> {
  late Future<Map<String, dynamic>> _future;

  @override
  void initState() {
    super.initState();
    _future = ref.read(authRepositoryProvider).fetchAddresses();
  }

  Future<void> _refresh() async {
    setState(() {
      _future = ref.read(authRepositoryProvider).fetchAddresses();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('عناويني')),
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
            final addresses = (data['addresses'] as List?) ?? const [];

            if (addresses.isEmpty) {
              return ListView(
                physics: const AlwaysScrollableScrollPhysics(),
                children: const [
                  SizedBox(height: 160),
                  Center(child: Text('لا توجد عناوين محفوظة')),
                ],
              );
            }

            return ListView.separated(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.all(16),
              itemCount: addresses.length,
              separatorBuilder: (_, __) => const SizedBox(height: 12),
              itemBuilder: (context, index) {
                final address = addresses[index] as Map<String, dynamic>;
                final isDefault = address['is_default'] == true;

                return Card(
                  child: ListTile(
                    leading: CircleAvatar(
                      backgroundColor: isDefault ? const Color(0xFF6D0E16) : Colors.grey.shade300,
                      child: Icon(Icons.location_on_outlined, color: isDefault ? Colors.white : Colors.grey.shade700),
                    ),
                    title: Text('${address['governorate'] ?? ''} - ${address['city'] ?? ''}'),
                    subtitle: Text(
                      [
                        address['address_details'],
                        address['nearest_landmark'],
                      ].whereType<String>().where((value) => value.trim().isNotEmpty).join('\n'),
                    ),
                    trailing: isDefault ? const Icon(Icons.check_circle, color: Colors.green) : null,
                  ),
                );
              },
            );
          },
        ),
      ),
    );
  }
}