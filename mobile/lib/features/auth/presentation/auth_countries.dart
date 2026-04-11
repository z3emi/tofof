class AuthCountry {
  final String nameAr;
  final String dialCode;
  final String flagEmoji;

  const AuthCountry({
    required this.nameAr,
    required this.dialCode,
    required this.flagEmoji,
  });

  String get dialDigits => dialCode.replaceAll('+', '');
}

const List<AuthCountry> authCountries = [
  AuthCountry(nameAr: 'العراق', dialCode: '+964', flagEmoji: '🇮🇶'),
  AuthCountry(nameAr: 'مصر', dialCode: '+20', flagEmoji: '🇪🇬'),
  AuthCountry(nameAr: 'السعودية', dialCode: '+966', flagEmoji: '🇸🇦'),
  AuthCountry(nameAr: 'الإمارات', dialCode: '+971', flagEmoji: '🇦🇪'),
  AuthCountry(nameAr: 'تركيا', dialCode: '+90', flagEmoji: '🇹🇷'),
  AuthCountry(nameAr: 'الأردن', dialCode: '+962', flagEmoji: '🇯🇴'),
  AuthCountry(nameAr: 'سوريا', dialCode: '+963', flagEmoji: '🇸🇾'),
  AuthCountry(nameAr: 'لبنان', dialCode: '+961', flagEmoji: '🇱🇧'),
  AuthCountry(nameAr: 'فلسطين', dialCode: '+970', flagEmoji: '🇵🇸'),
  AuthCountry(nameAr: 'قطر', dialCode: '+974', flagEmoji: '🇶🇦'),
  AuthCountry(nameAr: 'البحرين', dialCode: '+973', flagEmoji: '🇧🇭'),
  AuthCountry(nameAr: 'الكويت', dialCode: '+965', flagEmoji: '🇰🇼'),
  AuthCountry(nameAr: 'عُمان', dialCode: '+968', flagEmoji: '🇴🇲'),
  AuthCountry(nameAr: 'اليمن', dialCode: '+967', flagEmoji: '🇾🇪'),
  AuthCountry(nameAr: 'الجزائر', dialCode: '+213', flagEmoji: '🇩🇿'),
  AuthCountry(nameAr: 'تونس', dialCode: '+216', flagEmoji: '🇹🇳'),
  AuthCountry(nameAr: 'المغرب', dialCode: '+212', flagEmoji: '🇲🇦'),
  AuthCountry(nameAr: 'ليبيا', dialCode: '+218', flagEmoji: '🇱🇾'),
  AuthCountry(nameAr: 'السودان', dialCode: '+249', flagEmoji: '🇸🇩'),
  AuthCountry(nameAr: 'موريتانيا', dialCode: '+222', flagEmoji: '🇲🇷'),
  AuthCountry(nameAr: 'الصومال', dialCode: '+252', flagEmoji: '🇸🇴'),
];
