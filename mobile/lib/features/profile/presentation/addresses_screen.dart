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
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => _openAddressForm(),
        backgroundColor: const Color(0xFF6D0E16),
        foregroundColor: Colors.white,
        icon: const Icon(Icons.add_location_alt_outlined),
        label: const Text('إضافة موقع'),
      ),
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
            final addresses = (data['addresses'] as List?)?.whereType<Map<String, dynamic>>().toList() ?? <Map<String, dynamic>>[];

            if (addresses.isEmpty) {
              return ListView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(20),
                children: const [
                  SizedBox(height: 140),
                  Icon(Icons.location_off_outlined, size: 64, color: Colors.grey),
                  SizedBox(height: 10),
                  Center(child: Text('لا توجد عناوين محفوظة')),
                  SizedBox(height: 6),
                  Center(child: Text('اضغط إضافة موقع لحفظ عنوانك الأول')),
                ],
              );
            }

            return ListView.separated(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 100),
              itemCount: addresses.length,
              separatorBuilder: (_, __) => const SizedBox(height: 12),
              itemBuilder: (context, index) {
                final address = addresses[index];
                final isDefault = address['is_default'] == true;
                final addressId = (address['id'] as num?)?.toInt();

                return Container(
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(18),
                    border: Border.all(color: isDefault ? const Color(0xFF6D0E16) : const Color(0xFFE9E0E2)),
                    boxShadow: [
                      BoxShadow(
                        color: const Color(0xFF6D0E16).withValues(alpha: 0.05),
                        blurRadius: 12,
                        offset: const Offset(0, 8),
                      ),
                    ],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Icon(Icons.location_on_outlined, color: isDefault ? const Color(0xFF6D0E16) : Colors.grey.shade700),
                          const SizedBox(width: 6),
                          Expanded(
                            child: Text(
                              '${address['governorate'] ?? ''} - ${address['city'] ?? ''}',
                              style: const TextStyle(fontWeight: FontWeight.w800),
                            ),
                          ),
                          if (isDefault)
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                              decoration: BoxDecoration(
                                color: const Color(0xFF6D0E16).withValues(alpha: 0.12),
                                borderRadius: BorderRadius.circular(999),
                              ),
                              child: const Text('افتراضي', style: TextStyle(color: Color(0xFF6D0E16), fontWeight: FontWeight.w700, fontSize: 12)),
                            ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Text(address['address_details']?.toString() ?? ''),
                      if ((address['nearest_landmark']?.toString().trim().isNotEmpty ?? false)) ...[
                        const SizedBox(height: 4),
                        Text('أقرب نقطة: ${address['nearest_landmark']}', style: TextStyle(color: Colors.grey.shade700)),
                      ],
                      if (address['latitude'] != null || address['longitude'] != null) ...[
                        const SizedBox(height: 4),
                        Text(
                          'إحداثيات: ${address['latitude'] ?? '-'} , ${address['longitude'] ?? '-'}',
                          style: TextStyle(color: Colors.grey.shade700, fontSize: 12),
                        ),
                      ],
                      const SizedBox(height: 10),
                      Row(
                        children: [
                          TextButton.icon(
                            onPressed: addressId == null ? null : () => _openAddressForm(initial: address),
                            icon: const Icon(Icons.edit_outlined, size: 18),
                            label: const Text('تعديل'),
                          ),
                          const SizedBox(width: 8),
                          TextButton.icon(
                            onPressed: addressId == null ? null : () => _deleteAddress(addressId),
                            icon: const Icon(Icons.delete_outline, size: 18, color: Colors.red),
                            label: const Text('حذف', style: TextStyle(color: Colors.red)),
                          ),
                        ],
                      ),
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

  Future<void> _deleteAddress(int addressId) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('حذف العنوان'),
        content: const Text('هل أنت متأكد من حذف هذا العنوان؟'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('إلغاء')),
          FilledButton(onPressed: () => Navigator.pop(context, true), child: const Text('حذف')),
        ],
      ),
    );

    if (confirmed != true) return;

    try {
      await ref.read(authRepositoryProvider).deleteAddress(addressId);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('تم حذف العنوان')));
      await _refresh();
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    }
  }

  Future<void> _openAddressForm({Map<String, dynamic>? initial}) async {
    final governorateCtrl = TextEditingController(text: initial?['governorate']?.toString() ?? '');
    final cityCtrl = TextEditingController(text: initial?['city']?.toString() ?? '');
    final detailsCtrl = TextEditingController(text: initial?['address_details']?.toString() ?? '');
    final landmarkCtrl = TextEditingController(text: initial?['nearest_landmark']?.toString() ?? '');
    final latCtrl = TextEditingController(text: initial?['latitude']?.toString() ?? '');
    final lngCtrl = TextEditingController(text: initial?['longitude']?.toString() ?? '');
    var isDefault = initial?['is_default'] == true;

    await showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      showDragHandle: true,
      builder: (context) {
        return Padding(
          padding: EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
          child: StatefulBuilder(
            builder: (context, setModalState) {
              return SingleChildScrollView(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(initial == null ? 'إضافة عنوان جديد' : 'تعديل العنوان', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800)),
                    const SizedBox(height: 12),
                    TextField(controller: governorateCtrl, decoration: const InputDecoration(labelText: 'المحافظة')),
                    const SizedBox(height: 10),
                    TextField(controller: cityCtrl, decoration: const InputDecoration(labelText: 'المدينة')),
                    const SizedBox(height: 10),
                    TextField(controller: detailsCtrl, decoration: const InputDecoration(labelText: 'تفاصيل العنوان')),
                    const SizedBox(height: 10),
                    TextField(controller: landmarkCtrl, decoration: const InputDecoration(labelText: 'أقرب نقطة دالة (اختياري)')),
                    const SizedBox(height: 10),
                    Row(
                      children: [
                        Expanded(child: TextField(controller: latCtrl, keyboardType: const TextInputType.numberWithOptions(decimal: true), decoration: const InputDecoration(labelText: 'خط العرض'))),
                        const SizedBox(width: 10),
                        Expanded(child: TextField(controller: lngCtrl, keyboardType: const TextInputType.numberWithOptions(decimal: true), decoration: const InputDecoration(labelText: 'خط الطول'))),
                      ],
                    ),
                    const SizedBox(height: 10),
                    SwitchListTile(
                      value: isDefault,
                      onChanged: (value) => setModalState(() => isDefault = value),
                      title: const Text('اجعله العنوان الافتراضي'),
                      contentPadding: EdgeInsets.zero,
                    ),
                    const SizedBox(height: 12),
                    SizedBox(
                      width: double.infinity,
                      child: FilledButton(
                        onPressed: () async {
                          if (governorateCtrl.text.trim().isEmpty || cityCtrl.text.trim().isEmpty || detailsCtrl.text.trim().isEmpty) {
                            ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('املأ الحقول المطلوبة')));
                            return;
                          }

                          try {
                            if (initial == null) {
                              await ref.read(authRepositoryProvider).createAddress(
                                    governorate: governorateCtrl.text.trim(),
                                    city: cityCtrl.text.trim(),
                                    addressDetails: detailsCtrl.text.trim(),
                                    nearestLandmark: landmarkCtrl.text.trim(),
                                    latitude: _toDouble(latCtrl.text),
                                    longitude: _toDouble(lngCtrl.text),
                                    isDefault: isDefault,
                                  );
                            } else {
                              final addressId = (initial['id'] as num?)?.toInt();
                              if (addressId == null) {
                                throw 'عنوان غير صالح';
                              }
                              await ref.read(authRepositoryProvider).updateAddress(
                                    addressId: addressId,
                                    governorate: governorateCtrl.text.trim(),
                                    city: cityCtrl.text.trim(),
                                    addressDetails: detailsCtrl.text.trim(),
                                    nearestLandmark: landmarkCtrl.text.trim(),
                                    latitude: _toDouble(latCtrl.text),
                                    longitude: _toDouble(lngCtrl.text),
                                    isDefault: isDefault,
                                  );
                            }

                            if (!mounted) return;
                            Navigator.of(this.context).pop();
                            ScaffoldMessenger.of(this.context).showSnackBar(
                              SnackBar(content: Text(initial == null ? 'تمت إضافة العنوان' : 'تم تعديل العنوان')),
                            );
                            await _refresh();
                          } catch (e) {
                            if (!mounted) return;
                            ScaffoldMessenger.of(this.context).showSnackBar(SnackBar(content: Text(e.toString())));
                          }
                        },
                        child: Text(initial == null ? 'إضافة العنوان' : 'حفظ التعديلات'),
                      ),
                    ),
                  ],
                ),
              );
            },
          ),
        );
      },
    );
  }

  double? _toDouble(String value) {
    final v = value.trim();
    if (v.isEmpty) return null;
    return double.tryParse(v);
  }
}
