class ApiConstants {
  static const String baseUrl = String.fromEnvironment(
    'TOFOF_API_BASE_URL',
    defaultValue: 'https://www.tofofstore.com/api',
  );

  // Auth
  static const String register = '/auth/register';
  static const String login = '/auth/login';
  static const String requestOtp = '/auth/request-otp';
  static const String verifyOtp = '/auth/verify-otp';
  static const String requestPasswordResetOtp = '/auth/password-reset/request-otp';
  static const String resetPassword = '/auth/password-reset/confirm';
  static const String logout = '/auth/logout';
  static const String me = '/auth/me';

  // Store
  static const String sliders = '/store/sliders';
  static const String uiContent = '/store/ui-content';
  static const String sections = '/store/sections';
  static const String categories = '/store/categories';
  static const String products = '/store/products';
  static const String discountCodes = '/store/discount-codes';

  // Profile & Features
  static const String profile = '/profile';
  static const String profileUpdate = '/profile';
  static const String profilePasswordSendOtp = '/profile/password/send-otp';
  static const String profilePasswordChange = '/profile/password/change';
  static const String profileOrders = '/profile/orders';
  static const String profileDiscounts = '/profile/discounts';
  static const String cart = '/cart';
  static const String checkout = '/checkout';
  static const String orders = '/profile/orders';
  static const String wishlist = '/wishlist';
}
