import 'dart:ui';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:skeletonizer/skeletonizer.dart';

import '../../../shared/models/category_model.dart';
import '../providers/store_provider.dart';

class HomeView extends ConsumerStatefulWidget {
  final bool showAppBar;

  const HomeView({super.key, this.showAppBar = true});

  const HomeView.embedded({super.key, this.showAppBar = false});

  @override
  ConsumerState<HomeView> createState() => _HomeViewState();
}

class _HomeViewState extends ConsumerState<HomeView> {
  final PageController _heroController = PageController();
  int _heroIndex = 0;

  @override
  void dispose() {
    _heroController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final slidersAsync = ref.watch(slidersProvider);
    final categoriesAsync = ref.watch(categoriesProvider);
    final productsAsync = ref.watch(homeProductsProvider);

    final body = RefreshIndicator(
      onRefresh: () async {
        ref.invalidate(slidersProvider);
        ref.invalidate(categoriesProvider);
        ref.invalidate(homeProductsProvider);
      },
      color: const Color(0xFF6D0E16),
      child: CustomScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        slivers: [
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 0),
              child: _buildHeroSection(slidersAsync),
            ),
          ),
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(16, 18, 16, 0),
              child: _buildCategoriesSection(categoriesAsync),
            ),
          ),
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(16, 20, 16, 0),
              child: _buildBrandsSection(),
            ),
          ),
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(16, 22, 16, 0),
              child: _SectionHeader(
                title: 'جديدنا',
                actionLabel: 'عرض الكل',
                onTap: () {},
                accentLine: true,
              ),
            ),
          ),
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(16, 14, 16, 0),
              child: productsAsync.when(
                data: (products) => _FeaturedPairGrid(
                  products.take(2).toList(),
                  kind: _CardKind.newArrival,
                ),
                loading: () => Skeletonizer(
                  enabled: true,
                  child: _FeaturedPairGrid(
                    List.generate(2, (index) => _DummyProduct(index + 1)),
                    kind: _CardKind.newArrival,
                  ),
                ),
                error: (e, s) =>
                    _ErrorTile(message: 'تعذر تحميل المنتجات الآن'),
              ),
            ),
          ),
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(16, 20, 16, 0),
              child: _buildPromoBanner(slidersAsync),
            ),
          ),
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(16, 22, 16, 0),
              child: _SectionHeader(
                title: 'عروض مميزة',
                actionLabel: 'عرض الكل',
                onTap: () {},
              ),
            ),
          ),
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(16, 14, 16, 0),
              child: productsAsync.when(
                data: (products) => _OfferGrid(products.take(4).toList()),
                loading: () => Skeletonizer(
                  enabled: true,
                  child: _OfferGrid(
                    List.generate(4, (index) => _DummyProduct(index + 11)),
                  ),
                ),
                error: (e, s) => _ErrorTile(message: 'تعذر تحميل العروض'),
              ),
            ),
          ),
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(16, 22, 16, 0),
              child: _SectionHeader(
                title: 'الأكثر مبيعاً',
                actionLabel: 'عرض الكل',
                onTap: () {},
              ),
            ),
          ),
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(16, 14, 16, 0),
              child: productsAsync.when(
                data: (products) =>
                    _OfferGrid(products.skip(2).take(4).toList()),
                loading: () => Skeletonizer(
                  enabled: true,
                  child: _OfferGrid(
                    List.generate(4, (index) => _DummyProduct(index + 21)),
                  ),
                ),
                error: (e, s) => _ErrorTile(message: 'تعذر تحميل المنتجات'),
              ),
            ),
          ),
          const SliverToBoxAdapter(child: SizedBox(height: 120)),
        ],
      ),
    );

    if (!widget.showAppBar) {
      return body;
    }

    return Scaffold(
      appBar: PreferredSize(
        preferredSize: const Size.fromHeight(72),
        child: _StoreHeader(onSearch: () {}, onNotifications: () {}),
      ),
      body: body,
    );
  }

  Widget _buildHeroSection(AsyncValue<Map<String, dynamic>> slidersAsync) {
    return slidersAsync.when(
      data: (data) {
        final heroSlides =
            (data['hero'] as List?)?.cast<Map<String, dynamic>>() ?? const [];

        if (heroSlides.isEmpty) {
          return _HeroPlaceholder();
        }

        return Column(
          children: [
            AspectRatio(
              aspectRatio: 21 / 9,
              child: ClipRRect(
                borderRadius: BorderRadius.circular(28),
                child: PageView.builder(
                  controller: _heroController,
                  itemCount: heroSlides.length,
                  onPageChanged: (index) => setState(() => _heroIndex = index),
                  itemBuilder: (context, index) {
                    final slide = heroSlides[index];
                    return _HeroCard(
                      title: (slide['title'] ?? 'فخامة تتجاوز حدود الزمن')
                          .toString(),
                      subtitle:
                          (slide['subtitle'] ??
                                  'مجموعة مختارة بعناية لأرقى الساعات العالمية.')
                              .toString(),
                      buttonText: (slide['button_text'] ?? 'اكتشف المجموعة')
                          .toString(),
                      imageUrl: slide['image_url']?.toString(),
                      onTap: () {},
                    );
                  },
                ),
              ),
            ),
            const SizedBox(height: 12),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: List.generate(
                heroSlides.length,
                (index) => AnimatedContainer(
                  duration: const Duration(milliseconds: 250),
                  margin: const EdgeInsets.symmetric(horizontal: 3),
                  width: _heroIndex == index ? 22 : 12,
                  height: 4,
                  decoration: BoxDecoration(
                    color: _heroIndex == index
                        ? const Color(0xFF6D0E16)
                        : const Color(0xFFE0E0E0),
                    borderRadius: BorderRadius.circular(20),
                  ),
                ),
              ),
            ),
          ],
        );
      },
      loading: () => Skeletonizer(enabled: true, child: _HeroPlaceholder()),
      error: (e, s) => _HeroPlaceholder(),
    );
  }

  Widget _buildCategoriesSection(
    AsyncValue<List<CategoryModel>> categoriesAsync,
  ) {
    return categoriesAsync.when(
      data: (categories) {
        final visible = categories.take(6).toList();

        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _SectionHeader(
              title: 'الفئات',
              actionLabel: 'عرض الكل',
              onTap: () {},
            ),
            const SizedBox(height: 14),
            SizedBox(
              height: 104,
              child: ListView.separated(
                scrollDirection: Axis.horizontal,
                itemCount: visible.length,
                separatorBuilder: (_, __) => const SizedBox(width: 14),
                itemBuilder: (context, index) {
                  final category = visible[index];
                  return _CategoryCircle(
                    category: category,
                    onTap: () => context.push('/category/${category.id}'),
                  );
                },
              ),
            ),
          ],
        );
      },
      loading: () => Skeletonizer(
        enabled: true,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _SectionHeader(
              title: 'الفئات',
              actionLabel: 'عرض الكل',
              onTap: () {},
            ),
            const SizedBox(height: 14),
            SizedBox(
              height: 104,
              child: ListView.separated(
                scrollDirection: Axis.horizontal,
                itemCount: 5,
                separatorBuilder: (_, __) => const SizedBox(width: 14),
                itemBuilder: (context, index) =>
                    const _CategoryCirclePlaceholder(),
              ),
            ),
          ],
        ),
      ),
      error: (e, s) => const SizedBox.shrink(),
    );
  }

  Widget _buildBrandsSection() {
    const brands = [
      ('Rolex', 'روليكس'),
      ('Omega', 'أوميغا'),
      ('Gucci', 'غوتشي'),
      ('Cartier', 'كارتييه'),
      ('Tissot', 'تيسو'),
    ];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _SectionHeader(
          title: 'البراندات العالمية',
          actionLabel: 'عرض الكل',
          onTap: () {},
        ),
        const SizedBox(height: 14),
        SizedBox(
          height: 112,
          child: ListView.separated(
            scrollDirection: Axis.horizontal,
            itemCount: brands.length,
            separatorBuilder: (_, __) => const SizedBox(width: 14),
            itemBuilder: (context, index) {
              final brand = brands[index];
              return _BrandBadge(brand: brand.$1, label: brand.$2);
            },
          ),
        ),
      ],
    );
  }

  Widget _buildPromoBanner(AsyncValue<Map<String, dynamic>> slidersAsync) {
    return slidersAsync.when(
      data: (data) {
        final promoSlides =
            (data['promo_secondary'] as List?)?.cast<Map<String, dynamic>>() ??
            const [];
        final slide = promoSlides.isNotEmpty ? promoSlides.first : null;

        return Container(
          height: 178,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(28),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.08),
                blurRadius: 30,
                offset: const Offset(0, 10),
              ),
            ],
            image: slide?['image_url'] != null
                ? DecorationImage(
                    image: NetworkImage(slide!['image_url'].toString()),
                    fit: BoxFit.cover,
                  )
                : null,
            color: const Color(0xFF7B1220),
          ),
          child: ClipRRect(
            borderRadius: BorderRadius.circular(28),
            child: Stack(
              fit: StackFit.expand,
              children: [
                Container(color: Colors.black.withValues(alpha: 0.25)),
                Padding(
                  padding: const EdgeInsets.all(22),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisAlignment: MainAxisAlignment.end,
                    children: [
                      Text(
                        (slide?['title'] ?? 'عرض خاص').toString(),
                        style: GoogleFonts.notoSerif(
                          color: Colors.white,
                          fontSize: 24,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        (slide?['subtitle'] ?? 'لفترة محدودة فقط').toString(),
                        style: GoogleFonts.manrope(
                          color: Colors.white.withValues(alpha: 0.85),
                          fontSize: 13,
                          fontWeight: FontWeight.w400,
                        ),
                      ),
                      const SizedBox(height: 14),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 16,
                          vertical: 10,
                        ),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(14),
                        ),
                        child: Text(
                          (slide?['button_text'] ?? 'اكتشف العرض').toString(),
                          style: const TextStyle(
                            color: Color(0xFF6D0E16),
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        );
      },
      loading: () => Skeletonizer(
        enabled: true,
        child: Container(
          height: 178,
          decoration: BoxDecoration(
            color: Colors.grey.shade300,
            borderRadius: BorderRadius.circular(28),
          ),
        ),
      ),
      error: (e, s) => Container(
        height: 178,
        decoration: BoxDecoration(
          color: const Color(0xFF7B1220),
          borderRadius: BorderRadius.circular(28),
        ),
        child: const Center(
          child: Text(
            'عرض خاص',
            style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
          ),
        ),
      ),
    );
  }
}

class _StoreHeader extends StatelessWidget {
  final VoidCallback onSearch;
  final VoidCallback onNotifications;

  const _StoreHeader({required this.onSearch, required this.onNotifications});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        color: Color(0xCCFFFFFF),
        border: Border(bottom: BorderSide(color: Color(0xFFEEEEEE))),
      ),
      child: ClipRRect(
        child: BackdropFilter(
          filter: ImageFilter.blur(sigmaX: 12, sigmaY: 12),
          child: SafeArea(
            bottom: false,
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
              child: SizedBox(
                height: 48,
                child: Row(
                  textDirection: TextDirection.ltr,
                  children: [
                    _HeaderIconButton(icon: Icons.search, onTap: onSearch),
                    const Spacer(),
                    Image.asset(
                      'assets/images/logo.png',
                      height: 40,
                      fit: BoxFit.contain,
                    ),
                    const Spacer(),
                    _HeaderIconButton(
                      icon: Icons.notifications_none,
                      onTap: onNotifications,
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class _HeaderIconButton extends StatelessWidget {
  final IconData icon;
  final VoidCallback onTap;

  const _HeaderIconButton({required this.icon, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        borderRadius: BorderRadius.circular(999),
        onTap: onTap,
        child: Container(
          width: 44,
          height: 44,
          alignment: Alignment.center,
          child: Icon(icon, color: const Color(0xFF6D0E16), size: 28),
        ),
      ),
    );
  }
}

class _SectionHeader extends StatelessWidget {
  final String title;
  final String actionLabel;
  final VoidCallback onTap;
  final bool accentLine;

  const _SectionHeader({
    required this.title,
    required this.actionLabel,
    required this.onTap,
    this.accentLine = false,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              title,
              style: GoogleFonts.notoSerif(
                fontSize: 22,
                fontWeight: FontWeight.bold,
                color: const Color(0xFF6D0E16),
              ),
            ),
            if (accentLine) ...[
              const SizedBox(height: 4),
              Container(width: 34, height: 2, color: const Color(0xFFD59E06)),
            ],
          ],
        ),
        const Spacer(),
        TextButton(
          onPressed: onTap,
          style: TextButton.styleFrom(
            backgroundColor: const Color(0xFFD59E06).withValues(alpha: 0.08),
            foregroundColor: const Color(0xFFD59E06),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(999),
            ),
          ),
          child: Text(
            actionLabel,
            style: GoogleFonts.manrope(
              fontWeight: FontWeight.w800,
              fontSize: 12,
            ),
          ),
        ),
      ],
    );
  }
}

class _HeroCard extends StatelessWidget {
  final String title;
  final String subtitle;
  final String buttonText;
  final String? imageUrl;
  final VoidCallback onTap;

  const _HeroCard({
    required this.title,
    required this.subtitle,
    required this.buttonText,
    required this.imageUrl,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Stack(
      fit: StackFit.expand,
      children: [
        if (imageUrl != null)
          Image.network(
            imageUrl!,
            fit: BoxFit.cover,
            errorBuilder: (context, error, stackTrace) =>
                Container(color: const Color(0xFF8B5E5A)),
          )
        else
          Container(color: const Color(0xFF8B5E5A)),
        Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.topCenter,
              end: Alignment.bottomCenter,
              colors: [Color(0x1A6D0E16), Color(0xB36D0E16)],
            ),
          ),
        ),
        Padding(
          padding: const EdgeInsets.all(22),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisAlignment: MainAxisAlignment.end,
            children: [
              Text(
                title,
                style: GoogleFonts.notoSerif(
                  color: Colors.white,
                  fontSize: 24,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                subtitle,
                style: GoogleFonts.manrope(
                  color: Colors.white.withValues(alpha: 0.86),
                  fontSize: 13,
                  fontWeight: FontWeight.w300,
                ),
              ),
              const SizedBox(height: 14),
              Align(
                alignment: AlignmentDirectional.centerStart,
                child: ElevatedButton.icon(
                  onPressed: onTap,
                  icon: const Icon(Icons.arrow_back, size: 16),
                  label: Text(buttonText),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF6D0E16),
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14),
                    ),
                    padding: const EdgeInsets.symmetric(
                      horizontal: 16,
                      vertical: 12,
                    ),
                    textStyle: GoogleFonts.manrope(
                      fontWeight: FontWeight.w800,
                      fontSize: 12,
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}

class _HeroPlaceholder extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Container(
          height: 180,
          decoration: BoxDecoration(
            color: Colors.grey.shade300,
            borderRadius: BorderRadius.circular(28),
          ),
        ),
        const SizedBox(height: 12),
        Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: List.generate(
            2,
            (index) => Container(
              margin: const EdgeInsets.symmetric(horizontal: 3),
              width: 12,
              height: 4,
              decoration: BoxDecoration(
                color: Colors.grey.shade300,
                borderRadius: BorderRadius.circular(20),
              ),
            ),
          ),
        ),
      ],
    );
  }
}

class _CategoryCircle extends StatelessWidget {
  final CategoryModel category;
  final VoidCallback onTap;

  const _CategoryCircle({required this.category, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: SizedBox(
        width: 78,
        child: Column(
          children: [
            Container(
              width: 64,
              height: 64,
              decoration: BoxDecoration(
                color: const Color(0xFF6D0E16).withValues(alpha: 0.06),
                shape: BoxShape.circle,
                border: Border.all(
                  color: const Color(0xFF6D0E16).withValues(alpha: 0.1),
                ),
              ),
              child: category.imageUrl != null
                  ? ClipOval(
                      child: Image.network(
                        category.imageUrl!,
                        fit: BoxFit.cover,
                        errorBuilder: (context, error, stackTrace) => Icon(
                          _iconForCategory(category.name),
                          color: const Color(0xFF6D0E16),
                        ),
                      ),
                    )
                  : Icon(
                      _iconForCategory(category.name),
                      color: const Color(0xFF6D0E16),
                    ),
            ),
            const SizedBox(height: 8),
            Text(
              category.name,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
              textAlign: TextAlign.center,
              style: GoogleFonts.manrope(
                fontSize: 11,
                fontWeight: FontWeight.w800,
              ),
            ),
          ],
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

class _CategoryCirclePlaceholder extends StatelessWidget {
  const _CategoryCirclePlaceholder();

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: 78,
      child: Column(
        children: [
          Container(
            width: 64,
            height: 64,
            decoration: const BoxDecoration(
              color: Colors.white,
              shape: BoxShape.circle,
            ),
          ),
          const SizedBox(height: 8),
          Container(width: 44, height: 10, color: Colors.white),
        ],
      ),
    );
  }
}

class _BrandBadge extends StatelessWidget {
  final String brand;
  final String label;

  const _BrandBadge({required this.brand, required this.label});

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: 78,
      child: Column(
        children: [
          Container(
            width: 64,
            height: 64,
            decoration: BoxDecoration(
              color: Colors.white,
              shape: BoxShape.circle,
              border: Border.all(color: const Color(0xFFE9E2E1)),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.05),
                  blurRadius: 12,
                  offset: const Offset(0, 6),
                ),
              ],
            ),
            alignment: Alignment.center,
            child: Text(
              brand,
              textAlign: TextAlign.center,
              style: GoogleFonts.notoSerif(
                fontSize: 10,
                fontWeight: FontWeight.w800,
                letterSpacing: 0.6,
              ),
            ),
          ),
          const SizedBox(height: 8),
          Text(
            label,
            style: GoogleFonts.manrope(
              fontSize: 10,
              fontWeight: FontWeight.w800,
            ),
          ),
        ],
      ),
    );
  }
}

class _FeaturedPairGrid extends StatelessWidget {
  final List<dynamic> products;
  final _CardKind kind;

  const _FeaturedPairGrid(this.products, {required this.kind});

  @override
  Widget build(BuildContext context) {
    if (products.isEmpty) {
      return const _ErrorTile(message: 'لا توجد منتجات حالياً');
    }

    return Column(
      children: products.map((product) {
        return Padding(
          padding: const EdgeInsets.only(bottom: 32),
          child: _LuxuryProductCard(product: product, kind: kind),
        );
      }).toList(),
    );
  }
}

class _OfferGrid extends StatelessWidget {
  final List<dynamic> products;

  const _OfferGrid(this.products);

  @override
  Widget build(BuildContext context) {
    if (products.isEmpty) {
      return const _ErrorTile(message: 'لا توجد منتجات لعرضها');
    }

    return GridView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      itemCount: products.length,
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        mainAxisSpacing: 14,
        crossAxisSpacing: 14,
        childAspectRatio: 0.78,
      ),
      itemBuilder: (context, index) {
        final product = products[index];
        return _OfferProductCard(product: product);
      },
    );
  }
}

enum _CardKind { newArrival }

class _LuxuryProductCard extends StatelessWidget {
  final dynamic product;
  final _CardKind kind;

  const _LuxuryProductCard({required this.product, required this.kind});

  @override
  Widget build(BuildContext context) {
    final String name = product is _DummyProduct
        ? product.name as String
        : product.name as String;
    final String? imageUrl = product is _DummyProduct
        ? product.imageUrl as String?
        : product.imageUrl as String?;
    final double price = product is _DummyProduct
        ? product.currentPrice as double
        : product.currentPrice as double;
    final int id = product is _DummyProduct
        ? product.id as int
        : product.id as int;
    final String brand = _brandFromName(name);

    return GestureDetector(
      onTap: product is _DummyProduct
          ? null
          : () => context.push('/product/$id'),
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(32),
          border: Border.all(color: const Color(0x80EEEEEE)),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.1),
              blurRadius: 40,
              offset: const Offset(0, 10),
              spreadRadius: -15,
            ),
          ],
        ),
        child: Column(
          children: [
            AspectRatio(
              aspectRatio: 1,
              child: ClipRRect(
                borderRadius: const BorderRadius.vertical(
                  top: Radius.circular(32),
                ),
                child: Stack(
                  fit: StackFit.expand,
                  children: [
                    if (imageUrl != null)
                      Image.network(
                        imageUrl,
                        fit: BoxFit.cover,
                        errorBuilder: (context, error, stackTrace) => Container(
                          color: const Color(0xFFF6F2F1),
                          child: const Icon(Icons.image, size: 54),
                        ),
                      )
                    else
                      Container(
                        color: const Color(0xFFF6F2F1),
                        child: const Icon(Icons.image, size: 54),
                      ),

                    Container(
                      decoration: const BoxDecoration(
                        gradient: LinearGradient(
                          begin: Alignment.bottomCenter,
                          end: Alignment.topCenter,
                          colors: [
                            Color(0x99000000),
                            Colors.transparent,
                            Colors.transparent,
                          ],
                          stops: [0.0, 0.4, 1.0],
                        ),
                      ),
                    ),

                    Positioned(
                      top: 24,
                      left: 24,
                      child: Container(
                        width: 40,
                        height: 40,
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.2),
                          shape: BoxShape.circle,
                          border: Border.all(
                            color: Colors.white.withValues(alpha: 0.3),
                          ),
                        ),
                        child: ClipOval(
                          child: BackdropFilter(
                            filter: ImageFilter.blur(sigmaX: 8, sigmaY: 8),
                            child: const Icon(
                              Icons.favorite_border,
                              color: Colors.white,
                              size: 20,
                            ),
                          ),
                        ),
                      ),
                    ),

                    Positioned(
                      top: 24,
                      right: 24,
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 12,
                          vertical: 6,
                        ),
                        decoration: BoxDecoration(
                          color: const Color(0xFFD59E06),
                          borderRadius: BorderRadius.circular(999),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withValues(alpha: 0.2),
                              blurRadius: 10,
                              offset: const Offset(0, 4),
                            ),
                          ],
                        ),
                        child: const Text(
                          'إصدار محدود',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                            letterSpacing: 1.2,
                          ),
                        ),
                      ),
                    ),

                    Positioned(
                      left: 24,
                      right: 24,
                      bottom: 24,
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  brand.toUpperCase(),
                                  style: const TextStyle(
                                    color: Colors.white,
                                    fontSize: 10,
                                    fontWeight: FontWeight.bold,
                                    letterSpacing: 2.0,
                                    height: 1,
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  name,
                                  maxLines: 2,
                                  overflow: TextOverflow.ellipsis,
                                  style: GoogleFonts.notoSerif(
                                    color: Colors.white,
                                    fontSize: 18,
                                    fontWeight: FontWeight.bold,
                                    height: 1.2,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(width: 12),
                          Text(
                            '${price.toStringAsFixed(0)} د.ع',
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(24),
              child: Row(
                children: [
                  Expanded(
                    child: Text(
                      _descriptionFor(name),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: GoogleFonts.manrope(
                        fontSize: 12,
                        color: Colors.black.withValues(alpha: 0.6),
                        height: 1.6,
                      ),
                    ),
                  ),
                  const SizedBox(width: 16),
                  Container(
                    width: 48,
                    height: 48,
                    decoration: BoxDecoration(
                      color: const Color(0xFF6D0E16),
                      borderRadius: BorderRadius.circular(16),
                      boxShadow: [
                        BoxShadow(
                          color: const Color(0xFF6D0E16).withValues(alpha: 0.2),
                          blurRadius: 12,
                          offset: const Offset(0, 6),
                        ),
                      ],
                    ),
                    child: const Icon(
                      Icons.shopping_cart_outlined,
                      color: Colors.white,
                      size: 24,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  String _brandFromName(String name) {
    final lower = name.toLowerCase();
    if (lower.contains('rolex') || lower.contains('رول')) return 'Rolex';
    if (lower.contains('omega') || lower.contains('أوم')) return 'Omega';
    if (lower.contains('gucci') || lower.contains('غوت')) return 'Gucci';
    if (lower.contains('cartier') || lower.contains('كار')) return 'Cartier';
    return 'Tofof';
  }

  String _descriptionFor(String name) {
    if (name.contains('مون') || name.contains('Omega')) {
      return 'ساعة أنيقة بتفاصيل دقيقة ولمسة كلاسيكية.';
    }
    return 'تصميم فاخر يجمع الجودة مع الحضور الأنيق.';
  }
}

class _OfferProductCard extends StatelessWidget {
  final dynamic product;

  const _OfferProductCard({required this.product});

  @override
  Widget build(BuildContext context) {
    final String name = product is _DummyProduct
        ? product.name as String
        : product.name as String;
    final String? imageUrl = product is _DummyProduct
        ? product.imageUrl as String?
        : product.imageUrl as String?;
    final double price = product is _DummyProduct
        ? product.currentPrice as double
        : product.currentPrice as double;
    final int id = product is _DummyProduct
        ? product.id as int
        : product.id as int;
    final bool isOnSale = product is _DummyProduct
        ? false
        : (product.isOnSale as bool? ?? false);

    return GestureDetector(
      onTap: product is _DummyProduct
          ? null
          : () => context.push('/product/$id'),
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(22),
          border: Border.all(color: const Color(0xFFF1ECEB)),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Expanded(
              child: Stack(
                children: [
                  Positioned.fill(
                    child: ClipRRect(
                      borderRadius: const BorderRadius.vertical(
                        top: Radius.circular(22),
                      ),
                      child: imageUrl != null
                          ? Image.network(
                              imageUrl,
                              fit: BoxFit.cover,
                              errorBuilder: (context, error, stackTrace) =>
                                  Container(color: const Color(0xFFF4F0EF)),
                            )
                          : Container(color: const Color(0xFFF4F0EF)),
                    ),
                  ),
                  if (isOnSale)
                    Positioned(
                      top: 10,
                      right: 10,
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 4,
                        ),
                        decoration: BoxDecoration(
                          color: const Color(0xFFD59E06),
                          borderRadius: BorderRadius.circular(999),
                        ),
                        child: Text(
                          'عرض',
                          style: GoogleFonts.manrope(
                            color: Colors.white,
                            fontSize: 9,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ),
                    ),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    _brandFromName(name),
                    style: GoogleFonts.manrope(
                      fontSize: 9,
                      fontWeight: FontWeight.w800,
                      color: Colors.black.withValues(alpha: 0.45),
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    name,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: GoogleFonts.notoSerif(
                      fontSize: 13,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    '${price.toStringAsFixed(0)} د.ع',
                    style: GoogleFonts.manrope(
                      fontSize: 13,
                      fontWeight: FontWeight.w800,
                      color: const Color(0xFFD59E06),
                    ),
                  ),
                  const SizedBox(height: 8),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      onPressed: product is _DummyProduct
                          ? null
                          : () => context.push('/product/$id'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF6D0E16),
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(14),
                        ),
                        padding: const EdgeInsets.symmetric(vertical: 10),
                      ),
                      child: Text(
                        'أضف للسلة',
                        style: GoogleFonts.manrope(
                          fontSize: 10,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  String _brandFromName(String name) {
    final lower = name.toLowerCase();
    if (lower.contains('rolex') || lower.contains('رول')) return 'ROLEX';
    if (lower.contains('omega') || lower.contains('أوم')) return 'OMEGA';
    if (lower.contains('gucci') || lower.contains('غوت')) return 'GUCCI';
    if (lower.contains('cartier') || lower.contains('كار')) return 'CARTIER';
    return 'TOFOF';
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
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: const Color(0xFFF0EBEA)),
      ),
      child: Text(
        message,
        style: GoogleFonts.manrope(
          fontWeight: FontWeight.w600,
          color: Colors.black54,
        ),
      ),
    );
  }
}

class _DummyProduct {
  final int id;
  final String name;
  final double currentPrice;
  final String? imageUrl;
  final bool isOnSale;

  _DummyProduct(this.id)
    : name = 'منتج تجريبي أنيق',
      currentPrice = 25000,
      imageUrl = null,
      isOnSale = false;
}
