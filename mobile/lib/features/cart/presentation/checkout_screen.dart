import 'dart:math';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../../auth/data/auth_repository.dart';
import '../../auth/providers/auth_provider.dart';
import '../data/cart_repository.dart';
import '../providers/cart_provider.dart';

class CheckoutScreen extends ConsumerStatefulWidget {
  const CheckoutScreen({super.key});

  @override
  ConsumerState<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends ConsumerState<CheckoutScreen> {
  bool _isLoading = true;
  bool _isSubmitting = false;
  bool _useWallet = false;
  double _walletBalance = 0;
  int? _selectedAddressId;
  List<Map<String, dynamic>> _addresses = <Map<String, dynamic>>[];

  @override
  void initState() {
    super.initState();
    Future.microtask(_loadData);
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);
    try {
      await ref.read(cartProvider.notifier).fetchCart();
      final me = await ref.read(authRepositoryProvider).fetchMe();
      final addressesResponse = await ref
          .read(authRepositoryProvider)
          .fetchAddresses();
      final data =
          addressesResponse['data'] as Map<String, dynamic>? ??
          <String, dynamic>{};
      final addresses = ((data['addresses'] as List?) ?? const [])
          .whereType<Map<String, dynamic>>()
          .toList();

      int? selectedAddress;
      if (addresses.isNotEmpty) {
        final defaultAddress = addresses.firstWhere(
          (item) => item['is_default'] == true,
          orElse: () => addresses.first,
        );
        selectedAddress = (defaultAddress['id'] as num?)?.toInt();
      }

      if (!mounted) return;
      setState(() {
        _walletBalance = me.walletBalance;
        _addresses = addresses;
        _selectedAddressId = selectedAddress;
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() => _isLoading = false);
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text(e.toString())));
    }
  }

  Future<void> _addAddress() async {
    final rootContext = context;
    final governorateCtrl = TextEditingController();
    final cityCtrl = TextEditingController();
    final detailsCtrl = TextEditingController();
    final landmarkCtrl = TextEditingController();

    await showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      showDragHandle: true,
      builder: (context) {
        return Padding(
          padding: EdgeInsets.only(
            bottom: MediaQuery.of(context).viewInsets.bottom,
          ),
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                const Text(
                  'إضافة عنوان جديد',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: governorateCtrl,
                  decoration: const InputDecoration(labelText: 'المحافظة'),
                ),
                const SizedBox(height: 10),
                TextField(
                  controller: cityCtrl,
                  decoration: const InputDecoration(labelText: 'المدينة'),
                ),
                const SizedBox(height: 10),
                TextField(
                  controller: detailsCtrl,
                  decoration: const InputDecoration(
                    labelText: 'تفاصيل العنوان',
                  ),
                ),
                const SizedBox(height: 10),
                TextField(
                  controller: landmarkCtrl,
                  decoration: const InputDecoration(
                    labelText: 'أقرب نقطة دالة (اختياري)',
                  ),
                ),
                const SizedBox(height: 14),
                FilledButton(
                  onPressed: () async {
                    if (governorateCtrl.text.trim().isEmpty ||
                        cityCtrl.text.trim().isEmpty ||
                        detailsCtrl.text.trim().isEmpty) {
                      ScaffoldMessenger.of(rootContext).showSnackBar(
                        const SnackBar(content: Text('املأ الحقول المطلوبة')),
                      );
                      return;
                    }

                    try {
                      await ref
                          .read(authRepositoryProvider)
                          .createAddress(
                            governorate: governorateCtrl.text.trim(),
                            city: cityCtrl.text.trim(),
                            addressDetails: detailsCtrl.text.trim(),
                            nearestLandmark: landmarkCtrl.text.trim(),
                          );
                      if (!mounted) return;
                      if (!rootContext.mounted) return;
                      Navigator.of(rootContext).pop();
                      await _loadData();
                    } catch (e) {
                      if (!mounted) return;
                      if (!rootContext.mounted) return;
                      ScaffoldMessenger.of(
                        rootContext,
                      ).showSnackBar(SnackBar(content: Text(e.toString())));
                    }
                  },
                  child: const Text('حفظ العنوان'),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Future<void> _submitOrder() async {
    final cartState = ref.read(cartProvider);
    if (cartState.items.isEmpty) {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text('السلة فارغة')));
      return;
    }
    if (_selectedAddressId == null) {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text('اختر عنوان التوصيل')));
      return;
    }

    setState(() => _isSubmitting = true);
    try {
      final paymentMethod = _useWallet ? 'wallet+cod' : 'cod';
      final walletToUse = _useWallet
          ? min(_walletBalance, cartState.total)
          : 0.0;

      final response = await ref
          .read(cartRepositoryProvider)
          .placeOrder(
            addressId: _selectedAddressId!,
            paymentMethod: paymentMethod,
            useWalletAmount: walletToUse,
          );

      if (!mounted) return;
      await ref.read(cartProvider.notifier).fetchCart();
      await ref.read(authProvider.notifier).refreshMe();
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            response['message']?.toString() ?? 'تم إرسال الطلب بنجاح',
          ),
        ),
      );
      Navigator.of(context).pop();
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text(e.toString())));
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final cartState = ref.watch(cartProvider);
    final walletUsed = _useWallet ? min(_walletBalance, cartState.total) : 0.0;
    final payableAfterWallet = (cartState.total - walletUsed).clamp(
      0,
      double.infinity,
    );

    return Scaffold(
      appBar: AppBar(title: const Text('إتمام الطلب')),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  const Text(
                    'المنتجات',
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 10),
                  ...cartState.items.map(
                    (item) => Card(
                      child: ListTile(
                        leading: ClipRRect(
                          borderRadius: BorderRadius.circular(8),
                          child: Image.network(
                            item.imageUrl.isNotEmpty
                                ? item.imageUrl
                                : 'https://placehold.co/80x80',
                            width: 52,
                            height: 52,
                            fit: BoxFit.cover,
                            errorBuilder: (_, __, ___) =>
                                const Icon(Icons.image_outlined),
                          ),
                        ),
                        title: Text(
                          item.name,
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                        subtitle: Text('الكمية: ${item.quantity}'),
                        trailing: Text(
                          _formatPrice(item.total),
                          style: const TextStyle(fontWeight: FontWeight.w700),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 20),
                  const Text(
                    'عنوان الشحن',
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 10),
                  if (_addresses.isEmpty)
                    const Card(
                      child: Padding(
                        padding: EdgeInsets.all(16),
                        child: Text(
                          'لا توجد عناوين محفوظة، أضف عنوانًا جديدًا للمتابعة',
                        ),
                      ),
                    )
                  else
                    ..._addresses.map((address) {
                      final addressId = (address['id'] as num?)?.toInt();
                      return Card(
                        child: RadioListTile<int>(
                          value: addressId ?? -1,
                          groupValue: _selectedAddressId,
                          onChanged: addressId == null
                              ? null
                              : (v) => setState(() => _selectedAddressId = v),
                          title: Text(
                            '${address['governorate'] ?? ''} - ${address['city'] ?? ''}',
                          ),
                          subtitle: Text(
                            address['address_details']?.toString() ?? '',
                          ),
                        ),
                      );
                    }),
                  Align(
                    alignment: Alignment.centerRight,
                    child: TextButton.icon(
                      onPressed: _addAddress,
                      icon: const Icon(Icons.add_location_alt_outlined),
                      label: const Text('إضافة عنوان جديد'),
                    ),
                  ),
                  const SizedBox(height: 20),
                  const Text(
                    'طريقة الدفع',
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 8),
                  Card(
                    child: CheckboxListTile(
                      value: _useWallet,
                      onChanged: (v) => setState(() => _useWallet = v ?? false),
                      title: const Text('استخدام رصيد المحفظة'),
                      subtitle: Text(
                        'رصيدك المتاح: ${_formatPrice(_walletBalance)}',
                      ),
                    ),
                  ),
                  Card(
                    child: ListTile(
                      leading: const Icon(Icons.money, color: Colors.green),
                      title: const Text('الدفع عند الاستلام'),
                      trailing: const Icon(
                        Icons.check_circle,
                        color: Color(0xFF6D0E16),
                      ),
                    ),
                  ),
                  const SizedBox(height: 20),
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(12),
                      child: Column(
                        children: [
                          _summaryRow(
                            'المجموع الفرعي',
                            _formatPrice(cartState.subtotal),
                          ),
                          if (cartState.discount > 0)
                            _summaryRow(
                              'الخصم',
                              '-${_formatPrice(cartState.discount)}',
                              valueColor: Colors.green,
                            ),
                          if ((cartState.discountCode ?? '').trim().isNotEmpty)
                            _summaryRow(
                              'كود الخصم',
                              cartState.discountCode!.trim(),
                              valueColor: const Color(0xFF1A7F37),
                            ),
                          _summaryRow(
                            'الإجمالي',
                            _formatPrice(cartState.total),
                            isTotal: true,
                          ),
                          if (_useWallet) ...[
                            _summaryRow(
                              'المدفوع من المحفظة',
                              _formatPrice(walletUsed),
                              valueColor: const Color(0xFF005BBB),
                            ),
                            _summaryRow(
                              'المبلغ النهائي بعد المحفظة',
                              _formatPrice(payableAfterWallet),
                              isTotal: true,
                            ),
                          ],
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 18),
                  SizedBox(
                    height: 48,
                    child: ElevatedButton(
                      onPressed: _isSubmitting ? null : _submitOrder,
                      child: _isSubmitting
                          ? const SizedBox(
                              width: 20,
                              height: 20,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                color: Colors.white,
                              ),
                            )
                          : const Text('تأكيد الطلب'),
                    ),
                  ),
                ],
              ),
            ),
    );
  }

  Widget _summaryRow(
    String label,
    String value, {
    Color? valueColor,
    bool isTotal = false,
  }) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: TextStyle(
              fontWeight: isTotal ? FontWeight.w800 : FontWeight.w500,
            ),
          ),
          Text(
            value,
            style: TextStyle(
              color: valueColor ?? (isTotal ? const Color(0xFF6D0E16) : null),
              fontWeight: isTotal ? FontWeight.w800 : FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }

  String _formatPrice(num value) {
    return '${NumberFormat('#,##0.00').format(value)} د.ع';
  }
}
