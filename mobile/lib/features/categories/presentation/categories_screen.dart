import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:skeletonizer/skeletonizer.dart';

import '../../../core/theme/app_dimensions.dart';
import '../../home/providers/store_provider.dart';
import '../../../shared/models/category_model.dart';

class CategoriesScreen extends ConsumerWidget {
  final bool showAppBar;

  const CategoriesScreen({super.key, this.showAppBar = true});

  const CategoriesScreen.embedded({super.key, this.showAppBar = false});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final categoriesAsync = ref.watch(categoriesProvider);

    final body = RefreshIndicator(
      color: const Color(0xFF6D0E16),
      onRefresh: () async => ref.invalidate(categoriesProvider),
      child: CustomScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        slivers: [
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(
                AppDimensions.screenPadding,
                AppDimensions.screenPadding,
                AppDimensions.screenPadding,
                0,
              ),
              child: _IntroPanel(),
            ),
          ),
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(
                AppDimensions.screenPadding,
                20,
                AppDimensions.screenPadding,
                0,
              ),
              child: _SectionHeader(title: 'الفئات', actionLabel: 'عرض الكل'),
            ),
          ),
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(
                AppDimensions.screenPadding,
                14,
                AppDimensions.screenPadding,
                0,
              ),
              child: categoriesAsync.when(
                data: (categories) => _CategoriesGrid(categories),
                loading: () => Skeletonizer(
                  enabled: true,
                  child: _CategoriesGrid(
                    List.generate(6, (index) => _DummyCategory(index + 1)),
                  ),
                ),
                error: (e, s) => const _ErrorTile(message: 'تعذر تحميل الفئات'),
              ),
            ),
          ),
          const SliverToBoxAdapter(
            child: SizedBox(height: AppDimensions.bottomSafeGap),
          ),
        ],
      ),
    );

    if (!showAppBar) return body;

    return Scaffold(
      appBar: PreferredSize(
        preferredSize: const Size.fromHeight(AppDimensions.appBarHeight),
        child: _StoreHeader(onBack: () => context.pop(), onSearch: () {}),
      ),
      body: body,
    );
  }
}

class _StoreHeader extends StatelessWidget {
  final VoidCallback onBack;
  final VoidCallback onSearch;

  const _StoreHeader({required this.onBack, required this.onSearch});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.86),
        border: Border(
          bottom: BorderSide(color: Colors.grey.withValues(alpha: 0.16)),
        ),
      ),
      child: SafeArea(
        bottom: false,
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
          child: Row(
            textDirection: TextDirection.ltr,
            children: [
              IconButton(
                onPressed: onBack,
                icon: const Icon(
                  Icons.arrow_back_ios_new_rounded,
                  color: Color(0xFF6D0E16),
                ),
              ),
              const Spacer(),
              Image.asset('assets/images/logo.png', height: 32),
              const Spacer(),
              IconButton(
                onPressed: onSearch,
                icon: const Icon(Icons.search, color: Color(0xFF6D0E16)),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _IntroPanel extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Container(
      height: 168,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(AppDimensions.heroRadius),
        gradient: const LinearGradient(
          colors: [Color(0xFF4A0008), Color(0xFF6D0E16)],
          begin: Alignment.topRight,
          end: Alignment.bottomLeft,
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.08),
            blurRadius: 30,
            offset: const Offset(0, 10),
          ),
        ],
      ),
      child: Stack(
        children: [
          Positioned(
            right: 16,
            top: 16,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(AppDimensions.chipRadius),
              ),
              child: Text(
                'الأقسام',
                style: GoogleFonts.manrope(
                  color: Colors.white,
                  fontWeight: FontWeight.w800,
                  fontSize: 11,
                ),
              ),
            ),
          ),
          Positioned(
            left: 18,
            bottom: 18,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'اكتشف الفئات',
                  style: GoogleFonts.notoSerif(
                    color: Colors.white,
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  'مجموعة مختارة بعناية لتسهيل التصفح',
                  style: GoogleFonts.manrope(
                    color: Colors.white70,
                    fontSize: 12,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _SectionHeader extends StatelessWidget {
  final String title;
  final String actionLabel;

  const _SectionHeader({required this.title, required this.actionLabel});

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Text(
          title,
          style: GoogleFonts.notoSerif(
            fontSize: 22,
            fontWeight: FontWeight.bold,
            color: const Color(0xFF6D0E16),
          ),
        ),
        const Spacer(),
        Text(
          actionLabel,
          style: GoogleFonts.manrope(
            fontSize: 12,
            fontWeight: FontWeight.w800,
            color: const Color(0xFFD59E06),
          ),
        ),
      ],
    );
  }
}

class _CategoriesGrid extends StatelessWidget {
  final List<dynamic> categories;

  const _CategoriesGrid(this.categories);

  @override
  Widget build(BuildContext context) {
    return GridView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      itemCount: categories.length,
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        mainAxisSpacing: 14,
        crossAxisSpacing: 14,
        childAspectRatio: 1.45,
      ),
      itemBuilder: (context, index) {
        final cat = categories[index] is _DummyCategory
            ? categories[index] as _DummyCategory
            : categories[index] as CategoryModel;
        return _CategoryCard(
          category: cat,
          onTap: cat is CategoryModel
              ? () => context.push('/category/${cat.id}')
              : null,
        );
      },
    );
  }
}

class _CategoryCard extends StatelessWidget {
  final dynamic category;
  final VoidCallback? onTap;

  const _CategoryCard({required this.category, this.onTap});

  @override
  Widget build(BuildContext context) {
    final String name = category is _DummyCategory
        ? category.name as String
        : category.name as String;
    final String? imageUrl = category is _DummyCategory
        ? category.imageUrl as String?
        : category.imageUrl as String?;
    final int count = category is _DummyCategory
        ? 0
        : (category.productsCount as int? ?? 0);
    final IconData icon = _iconForCategory(name);

    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(AppDimensions.cardRadius),
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(AppDimensions.cardRadius),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.05),
              blurRadius: 18,
              offset: const Offset(0, 8),
            ),
          ],
        ),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  Container(
                    width: 48,
                    height: 48,
                    decoration: BoxDecoration(
                      color: const Color(0xFF6D0E16).withValues(alpha: 0.06),
                      borderRadius: BorderRadius.circular(
                        AppDimensions.innerRadius,
                      ),
                    ),
                    child: imageUrl != null
                        ? ClipRRect(
                            borderRadius: BorderRadius.circular(
                              AppDimensions.innerRadius,
                            ),
                            child: Image.network(
                              imageUrl,
                              fit: BoxFit.cover,
                              errorBuilder: (c, e, s) =>
                                  Icon(icon, color: const Color(0xFF6D0E16)),
                            ),
                          )
                        : Icon(icon, color: const Color(0xFF6D0E16)),
                  ),
                  const Spacer(),
                  Container(
                    width: 10,
                    height: 10,
                    decoration: const BoxDecoration(
                      color: Color(0xFFD59E06),
                      shape: BoxShape.circle,
                    ),
                  ),
                ],
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    name,
                    style: GoogleFonts.notoSerif(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: const Color(0xFF1A1C1C),
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    '$count منتج',
                    style: GoogleFonts.manrope(
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                      color: Colors.black54,
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  IconData _iconForCategory(String name) {
    final lower = name.toLowerCase();
    if (lower.contains('ساع') || lower.contains('watch')) return Icons.watch;
    if (lower.contains('محفظ') || lower.contains('wallet'))
      return Icons.account_balance_wallet;
    if (lower.contains('عطر') || lower.contains('perfume')) return Icons.spa;
    if (lower.contains('إكس') || lower.contains('access')) return Icons.diamond;
    return Icons.category;
  }
}

class _ErrorTile extends StatelessWidget {
  final String message;

  const _ErrorTile({required this.message});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(AppDimensions.cardRadius),
      ),
      child: Text(
        message,
        style: GoogleFonts.manrope(fontWeight: FontWeight.w600),
      ),
    );
  }
}

class _DummyCategory {
  final int id;
  final String name;
  final String? imageUrl;

  _DummyCategory(this.id) : name = 'قسم فاخر', imageUrl = null;
}
