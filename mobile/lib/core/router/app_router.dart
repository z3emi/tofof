import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../features/main/presentation/main_screen.dart';
import '../../features/home/presentation/home_view.dart';
import '../../features/categories/presentation/categories_screen.dart';
import '../../features/cart/presentation/cart_screen.dart';
import '../../features/profile/presentation/profile_screen.dart';
import '../../features/auth/presentation/login_screen.dart';
import '../../features/auth/presentation/register_screen.dart';
import '../../features/products/presentation/product_details_screen.dart';
import '../../features/categories/presentation/category_products_screen.dart';

final appRouterProvider = Provider<GoRouter>((ref) {
  return GoRouter(
    initialLocation: '/',
    debugLogDiagnostics: true,
    routes: [
      ShellRoute(
        builder: (context, state, child) {
          return MainScreen(child: child);
        },
        routes: [
          GoRoute(
            path: '/',
            builder: (context, state) => const HomeView(),
          ),
          GoRoute(
            path: '/categories',
            builder: (context, state) => const CategoriesScreen(),
          ),
          GoRoute(
            path: '/cart',
            builder: (context, state) => const CartScreen(),
          ),
          GoRoute(
            path: '/profile',
            builder: (context, state) => const ProfileScreen(),
          ),
          // Product details inside ShellRoute to keep bottom nav visible
          GoRoute(
            path: '/product/:id',
            builder: (context, state) {
              final id = int.tryParse(state.pathParameters['id'] ?? '0') ?? 0;
              return ProductDetailsScreen(productId: id);
            },
          ),
          GoRoute(
            path: '/category/:id',
            builder: (context, state) {
              final id = int.tryParse(state.pathParameters['id'] ?? '0') ?? 0;
              return CategoryProductsScreen(categoryId: id);
            },
          ),
        ],
      ),
      // Login and register can either be in ShellRoute or outside. We'll put them in ShellRoute to keep standard behavior,
      // but without the bottom nav if we prefer, but for now we let ShellRoute handle it.
      GoRoute(
        path: '/login',
        builder: (context, state) => const LoginScreen(), // Covered by ShellRoute if moved inside, but here it's outside. wait...
        // Let's put login inside ShellRoute so we can navigate back gracefully
      ),
      GoRoute(
        path: '/register',
        builder: (context, state) => const RegisterScreen(),
      ),
    ],
  );
});
