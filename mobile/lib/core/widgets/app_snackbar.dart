import 'package:flutter/material.dart';

void showTimedSnackBar(
  BuildContext context,
  String message, {
  Color backgroundColor = const Color(0xFF6D0E16),
  Duration duration = const Duration(seconds: 3),
}) {
  final messenger = ScaffoldMessenger.of(context);
  messenger.hideCurrentSnackBar();
  messenger.showSnackBar(
    SnackBar(
      duration: duration,
      backgroundColor: backgroundColor,
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
      content: _TimedSnackBarContent(message: message, duration: duration),
    ),
  );
}

class _TimedSnackBarContent extends StatelessWidget {
  final String message;
  final Duration duration;

  const _TimedSnackBarContent({required this.message, required this.duration});

  @override
  Widget build(BuildContext context) {
    return Column(
      mainAxisSize: MainAxisSize.min,
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          message,
          style: const TextStyle(
            color: Colors.white,
            fontWeight: FontWeight.w600,
            fontSize: 13,
            height: 1.2,
          ),
          maxLines: 2,
          overflow: TextOverflow.ellipsis,
        ),
        const SizedBox(height: 6),
        ClipRRect(
          borderRadius: BorderRadius.circular(999),
          child: Container(
            height: 2,
            color: Colors.white.withValues(alpha: 0.28),
            child: TweenAnimationBuilder<double>(
              tween: Tween(begin: 1, end: 0),
              duration: duration,
              builder: (context, value, child) {
                return Align(
                  alignment: Alignment.centerLeft,
                  child: FractionallySizedBox(
                    widthFactor: value,
                    child: Container(
                      color: Colors.white.withValues(alpha: 0.92),
                    ),
                  ),
                );
              },
            ),
          ),
        ),
      ],
    );
  }
}
