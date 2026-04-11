import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/theme/app_settings_provider.dart';

class SettingsScreen extends ConsumerWidget {
  const SettingsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final settings = ref.watch(appSettingsProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('إعدادات التطبيق')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Card(
            child: Column(
              children: [
                SwitchListTile.adaptive(
                  value: settings.notificationsEnabled,
                  title: const Text('الإشعارات'),
                  subtitle: const Text('تفعيل أو إيقاف إشعارات التطبيق'),
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
                  title: const Text('مظهر التطبيق'),
                  subtitle: const Text('ليلي / نهاري / حسب النظام'),
                ),
                Padding(
                  padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
                  child: SegmentedButton<ThemeMode>(
                    segments: const [
                      ButtonSegment<ThemeMode>(
                        value: ThemeMode.light,
                        label: Text('نهاري'),
                        icon: Icon(Icons.light_mode_outlined),
                      ),
                      ButtonSegment<ThemeMode>(
                        value: ThemeMode.dark,
                        label: Text('ليلي'),
                        icon: Icon(Icons.dark_mode_outlined),
                      ),
                      ButtonSegment<ThemeMode>(
                        value: ThemeMode.system,
                        label: Text('النظام'),
                        icon: Icon(Icons.settings_suggest_outlined),
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
              children: const [
                ListTile(
                  leading: Icon(Icons.language_outlined),
                  title: Text('اللغة'),
                  subtitle: Text('العربية'),
                ),
                Divider(height: 0),
                ListTile(
                  leading: Icon(Icons.info_outline),
                  title: Text('حول التطبيق'),
                  subtitle: Text('إصدار 1.0.0'),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
