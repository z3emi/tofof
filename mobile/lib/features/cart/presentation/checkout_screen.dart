import 'package:flutter/material.dart';

class CheckoutScreen extends StatefulWidget {
  const CheckoutScreen({super.key});

  @override
  State<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends State<CheckoutScreen> {
  int _selectedAddress = 1;
  bool _useWallet = false;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('إتمام الطلب')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            const Text('عنوان الشحن', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
            const SizedBox(height: 12),
            Card(
              child: RadioListTile<int>(
                value: 1,
                groupValue: _selectedAddress,
                onChanged: (v) => setState(() => _selectedAddress = v!),
                title: const Text('المنزل'),
                subtitle: const Text('بغداد، الكرادة، شارع 62'),
                secondary: IconButton(icon: const Icon(Icons.edit), onPressed: () {}),
              ),
            ),
            TextButton.icon(
              onPressed: () {},
              icon: const Icon(Icons.add),
              label: const Text('إضافة عنوان جديد'),
            ),
            
            const SizedBox(height: 24),
            const Text('طريقة الدفع', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
            const SizedBox(height: 12),
            Card(
              child: CheckboxListTile(
                value: _useWallet,
                onChanged: (v) => setState(() => _useWallet = v ?? false),
                title: const Text('استخدام رصيد المحفظة'),
                subtitle: const Text('رصيدك المتاح: 50,000 د.ع'),
              ),
            ),
            Card(
              child: ListTile(
                leading: const Icon(Icons.money, color: Colors.green),
                title: const Text('الدفع عند الاستلام'),
                trailing: const Icon(Icons.check_circle, color: Color(0xFF6D0E16)),
              ),
            ),

            const SizedBox(height: 40),
            ElevatedButton(
              onPressed: () {
                // Submit order logic via API
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('تم إرسال طلبك بنجاح!')),
                );
              },
              style: ElevatedButton.styleFrom(padding: const EdgeInsets.all(16)),
              child: const Text('تأكيد الطلب'),
            )
          ],
        ),
      ),
    );
  }
}
