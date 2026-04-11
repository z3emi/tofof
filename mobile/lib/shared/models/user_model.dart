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

  UserModel copyWith({
    int? id,
    String? name,
    String? email,
    String? phoneNumber,
    String? avatar,
    double? walletBalance,
    String? referralCode,
  }) {
    return UserModel(
      id: id ?? this.id,
      name: name ?? this.name,
      email: email ?? this.email,
      phoneNumber: phoneNumber ?? this.phoneNumber,
      avatar: avatar ?? this.avatar,
      walletBalance: walletBalance ?? this.walletBalance,
      referralCode: referralCode ?? this.referralCode,
    );
  }

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: _asInt(json['id']) ?? 0,
      name: json['name'] as String? ?? '',
      email: json['email'] as String? ?? '',
      phoneNumber: json['phone_number'] as String? ?? '',
      avatar: json['avatar'] as String?,
      walletBalance: _asDouble(json['wallet_balance']) ?? 0.0,
      referralCode: json['referral_code'] as String?,
    );
  }

  static int? _asInt(dynamic value) {
    if (value is int) return value;
    if (value is num) return value.toInt();
    if (value is String) return int.tryParse(value);
    return null;
  }

  static double? _asDouble(dynamic value) {
    if (value is double) return value;
    if (value is int) return value.toDouble();
    if (value is num) return value.toDouble();
    if (value is String) return double.tryParse(value);
    return null;
  }
}
