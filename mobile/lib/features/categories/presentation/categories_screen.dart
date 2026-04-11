import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:skeletonizer/skeletonizer.dart';

import '../../home/providers/store_provider.dart';

class CategoriesScreen extends ConsumerWidget {
  final bool showAppBar;

  const CategoriesScreen({super.key, this.showAppBar = true});

  const CategoriesScreen.embedded({super.key, this.showAppBar = false});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final categoriesAsync = ref.watch(categoriesProvider);
    final body = categoriesAsync.when(
      data: (categories) => ListView.builder(
        padding: const EdgeInsets.fromLTRB(12, 12, 12, 110),
        itemCount: categories.length,
        itemBuilder: (context, index) {
          final cat = categories[index];
          return Card(
            margin: const EdgeInsets.symmetric(vertical: 6),
            child: ListTile(
              leading: cat.imageUrl != null
                  ? CircleAvatar(backgroundImage: NetworkImage(cat.imageUrl!))
                  : const CircleAvatar(child: Icon(Icons.category)),
              title: Text(cat.name),
              trailing: const Icon(Icons.arrow_forward_ios, size: 16),
              onTap: () => context.push('/category/${cat.id}'),
            ),
          );
        },
      ),
      loading: () => Skeletonizer(
        enabled: true,
        child: ListView.builder(
          padding: const EdgeInsets.fromLTRB(12, 12, 12, 110),
          itemCount: 8,
          itemBuilder: (context, index) => const Card(
            margin: EdgeInsets.symmetric(vertical: 6),
            child: ListTile(
              leading: CircleAvatar(child: Icon(Icons.category)),
              title: Text('قسم تجريبي'),
              trailing: Icon(Icons.arrow_forward_ios, size: 16),
            ),
          ),
        ),
      ),
      error: (e, s) => Center(child: Text('خطأ: $e')),
    );

    if (!showAppBar) {
      return body;
    }

    return Scaffold(
      appBar: AppBar(title: const Text('الأقسام')),
      body: body,
    );
  }
}
