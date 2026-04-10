class UserModel {
  final int id;
  final String name;
  final String email;
  final String phoneNumber;
  final String? avatar;
  final double walletBalance;
  final String? referralCode;

  UserModel({
    required this.id,
    required this.name,
    required this.email,
    required this.phoneNumber,
    this.avatar,
    this.walletBalance = 0.0,
    this.referralCode,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'] as int,
      name: json['name'] as String? ?? '',
      email: json['email'] as String? ?? '',
      phoneNumber: json['phone_number'] as String? ?? '',
      avatar: json['avatar'] as String?,
      walletBalance: (json['wallet_balance'] as num?)?.toDouble() ?? 0.0,
      referralCode: json['referral_code'] as String?,
    );
  }
}
