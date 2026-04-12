import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/theme/app_settings_provider.dart';

class SettingsScreen extends ConsumerWidget {
  const SettingsScreen({super.key});

  String _text(bool isArabic, String ar, String en) => isArabic ? ar : en;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final settings = ref.watch(appSettingsProvider);
    final isArabic = Localizations.localeOf(context).languageCode == 'ar';

    return Scaffold(
      appBar: AppBar(
        title: Text(_text(isArabic, 'إعدادات التطبيق', 'App Settings')),
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Card(
            child: Column(
              children: [
                SwitchListTile.adaptive(
                  value: settings.notificationsEnabled,
                  title: Text(_text(isArabic, 'الإشعارات', 'Notifications')),
                  subtitle: Text(
                    _text(
                      isArabic,
                      'تفعيل أو إيقاف إشعارات التطبيق',
                      'Enable or disable app notifications',
                    ),
                  ),
                  secondary: const Icon(Icons.notifications_active_outlined),
                  onChanged: (value) {
                    ref
                        .read(appSettingsProvider.notifier)
                        .setNotificationsEnabled(value);
                  },
                ),
                const Divider(height: 0),
                ListTile(
                  leading: const Icon(Icons.palette_outlined),
                  title: Text(_text(isArabic, 'مظهر التطبيق', 'Appearance')),
                  subtitle: Text(
                    _text(
                      isArabic,
                      'ليلي / نهاري / حسب النظام',
                      'Dark / Light / System',
                    ),
                  ),
                ),
                Padding(
                  padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
                  child: SegmentedButton<ThemeMode>(
                    segments: [
                      ButtonSegment<ThemeMode>(
                        value: ThemeMode.light,
                        label: Text(_text(isArabic, 'نهاري', 'Light')),
                        icon: const Icon(Icons.light_mode_outlined),
                      ),
                      ButtonSegment<ThemeMode>(
                        value: ThemeMode.dark,
                        label: Text(_text(isArabic, 'ليلي', 'Dark')),
                        icon: const Icon(Icons.dark_mode_outlined),
                      ),
                      ButtonSegment<ThemeMode>(
                        value: ThemeMode.system,
                        label: Text(_text(isArabic, 'النظام', 'System')),
                        icon: const Icon(Icons.settings_suggest_outlined),
                      ),
                    ],
                    selected: {settings.themeMode},
                    onSelectionChanged: (selection) {
                      if (selection.isNotEmpty) {
                        ref
                            .read(appSettingsProvider.notifier)
                            .setThemeMode(selection.first);
                      }
                    },
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 12),
          Card(
            child: Column(
              children: [
                ListTile(
                  leading: const Icon(Icons.language_outlined),
                  title: Text(_text(isArabic, 'اللغة', 'Language')),
                  subtitle: Text(
                    _text(isArabic, 'العربية / English', 'English / العربية'),
                  ),
                ),
                Padding(
                  padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
                  child: SegmentedButton<String>(
                    segments: [
                      ButtonSegment<String>(
                        value: 'ar',
                        label: Text(_text(isArabic, 'العربية', 'Arabic')),
                      ),
                      ButtonSegment<String>(
                        value: 'en',
                        label: Text(_text(isArabic, 'الإنكليزية', 'English')),
                      ),
                    ],
                    selected: {settings.locale.languageCode},
                    onSelectionChanged: (selection) {
                      if (selection.isNotEmpty) {
                        ref
                            .read(appSettingsProvider.notifier)
                            .setLocale(Locale(selection.first));
                      }
                    },
                  ),
                ),
                const Divider(height: 0),
                ListTile(
                  leading: const Icon(Icons.info_outline),
                  title: Text(_text(isArabic, 'حول التطبيق', 'About App')),
                  subtitle: const Text('Version 1.0.0'),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
