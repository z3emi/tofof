import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../features/main/presentation/main_screen.dart';
import '../../features/auth/presentation/login_screen.dart';
import '../../features/auth/presentation/register_screen.dart';
import '../../features/auth/presentation/auth_otp_screen.dart';
import '../../features/auth/presentation/password_reset_screen.dart';
import '../../features/products/presentation/product_details_screen.dart';
import '../../features/categories/presentation/category_products_screen.dart';
import '../../features/products/presentation/favorites_screen.dart';
import '../../features/cart/presentation/checkout_screen.dart';
import '../../features/profile/presentation/edit_profile_screen.dart';
import '../../features/profile/presentation/orders_screen.dart';
import '../../features/profile/presentation/discounts_screen.dart';
import '../../features/profile/presentation/addresses_screen.dart';
import '../../features/profile/presentation/settings_screen.dart';

final appRouterProvider = Provider<GoRouter>((ref) {
  return GoRouter(
    initialLocation: '/',
    debugLogDiagnostics: true,
    routes: [
      GoRoute(
        path: '/',
        name: 'home',
        builder: (context, state) => const MainScreen(),
      ),
      GoRoute(
        path: '/product/:id',
        name: 'product_details',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '0') ?? 0;
          return ProductDetailsScreen(productId: id);
        },
      ),
      GoRoute(
        path: '/category/:id',
        name: 'category_products',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '0') ?? 0;
          return CategoryProductsScreen(categoryId: id);
        },
      ),
      GoRoute(
        path: '/login',
        name: 'login',
        builder: (context, state) => const LoginScreen(),
      ),
      GoRoute(
        path: '/register',
        name: 'register',
        builder: (context, state) => const RegisterScreen(),
      ),
      GoRoute(
        path: '/verify-otp',
        name: 'verify_otp',
        builder: (context, state) => const AuthOtpScreen(),
      ),
      GoRoute(
        path: '/reset-password',
        name: 'reset_password',
        builder: (context, state) => const PasswordResetScreen(),
      ),
      GoRoute(
        path: '/checkout',
        name: 'checkout',
        builder: (context, state) => const CheckoutScreen(),
      ),
      GoRoute(
        path: '/profile/edit',
        name: 'edit_profile',
        builder: (context, state) => const EditProfileScreen(),
      ),
      GoRoute(
        path: '/profile/orders',
        name: 'profile_orders',
        builder: (context, state) => const OrdersScreen(),
      ),
      GoRoute(
        path: '/profile/addresses',
        name: 'profile_addresses',
        builder: (context, state) => const AddressesScreen(),
      ),
      GoRoute(
        path: '/profile/favorites',
        name: 'profile_favorites',
        builder: (context, state) => const FavoritesScreen(),
      ),
      GoRoute(
        path: '/profile/discounts',
        name: 'profile_discounts',
        builder: (context, state) => const DiscountsScreen(),
      ),
      GoRoute(
        path: '/settings',
        name: 'settings',
        builder: (context, state) => const SettingsScreen(),
      ),
    ],
  );
});
