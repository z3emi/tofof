<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReviewModerationService
{
    /**
     * Analyze review comment and return moderation decision.
     *
     * @return array{status:string, score:int, flags:array<int,string>}
     */
    public function moderate(?string $comment): array
    {
        $text = trim((string) $comment);

        if ($text === '') {
            return [
                'status' => 'approved',
                'score' => 0,
                'flags' => [],
            ];
        }

        $config = (array) config('moderation.reviews', []);

        $flags = [];
        $score = 0;

        $external = $this->moderateUsingExternalApi($text);
        if ($external !== null) {
            $flags = array_merge($flags, (array) ($external['flags'] ?? []));
        }

        $normalized = $this->normalizeForWordMatch($text);
        $compactNormalized = preg_replace('/\s+/u', '', $normalized) ?? $normalized;

        $blockedWords = (array) ($config['blocked_words'] ?? []);
        $foundBlockedWords = [];
        foreach ($blockedWords as $word) {
            $w = $this->normalizeForWordMatch((string) $word);
            if ($w === '') {
                continue;
            }

            $compactWord = preg_replace('/\s+/u', '', $w) ?? $w;

            if (str_contains($normalized, $w) || str_contains($compactNormalized, $compactWord)) {
                $foundBlockedWords[] = $w;
            }
        }

        if (! empty($foundBlockedWords)) {
            $flags[] = 'blocked_words';
        }

        $urlCount = $this->extractUrlCount($text);
        if ($urlCount > 0) {
            $flags[] = 'contains_links';
        }

        if ($urlCount >= 2) {
            $flags[] = 'link_spam';
        }

        if (preg_match('/(.)\1{5,}/u', $text) === 1) {
            $flags[] = 'repeated_characters';
        }

        if (preg_match('/([[:punct:]])\1{4,}/u', $text) === 1) {
            $flags[] = 'repeated_symbols';
        }

        if ($this->hasSuspiciousShortenerUrl($text)) {
            $flags[] = 'suspicious_short_links';
        }

        $flags = array_values(array_unique($flags));
        $status = empty($flags) ? 'approved' : 'pending';
        $score = empty($flags) ? 0 : 1;

        return [
            'status' => $status,
            'score' => $score,
            'flags' => $flags,
        ];
    }

    /**
     * External moderation API is optional and config-driven.
     * Returns normalized score/flags, or null when disabled/unavailable.
     *
     * @return array{score:int,flags:array<int,string>}|null
     */
    private function moderateUsingExternalApi(string $text): ?array
    {
        $cfg = (array) config('moderation.reviews.external_api', []);
        $enabled = (bool) ($cfg['enabled'] ?? false);
        $url = trim((string) ($cfg['url'] ?? ''));

        if (! $enabled || $url === '') {
            return null;
        }

        $timeout = (int) ($cfg['timeout_seconds'] ?? 5);
        $payloadMode = (string) ($cfg['payload_mode'] ?? 'default');

        $payload = $this->buildExternalPayload($text, $payloadMode);

        try {
            $request = Http::timeout(max(1, $timeout))->acceptJson();

            $token = trim((string) ($cfg['token'] ?? ''));
            if ($token !== '') {
                $request = $request->withToken($token);
            }

            $customHeaders = (array) ($cfg['headers'] ?? []);
            if (! empty($customHeaders)) {
                $request = $request->withHeaders($customHeaders);
            }

            $response = $request->post($url, $payload);

            if (! $response->ok()) {
                Log::warning('Review moderation API non-200 response', [
                    'status' => $response->status(),
                    'url' => $url,
                ]);

                return null;
            }

            $data = (array) $response->json();

            return $this->normalizeExternalModerationResponse($data);
        } catch (\Throwable $e) {
            Log::warning('Review moderation API failed, fallback to local filters', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Supports common API payload contracts.
     */
    private function buildExternalPayload(string $text, string $mode): array
    {
        return match ($mode) {
            'openai_like' => [
                'input' => $text,
            ],
            'perspective_like' => [
                'comment' => ['text' => $text],
            ],
            default => [
                'text' => $text,
                'content' => $text,
                'language' => 'auto',
            ],
        };
    }

    /**
     * Try to normalize multiple provider response formats.
     *
     * @return array{score:int,flags:array<int,string>}|null
     */
    private function normalizeExternalModerationResponse(array $data): ?array
    {
        $score = 0;
        $flags = [];

        if (isset($data['results'][0]) && is_array($data['results'][0])) {
            $result = $data['results'][0];
            $flagged = (bool) ($result['flagged'] ?? false);
            $categories = (array) ($result['categories'] ?? []);
            $categoryScores = (array) ($result['category_scores'] ?? []);

            foreach ($categories as $name => $value) {
                if ((bool) $value) {
                    $flags[] = 'ext_' . (string) $name;
                }
            }

            foreach ($categoryScores as $value) {
                $v = is_numeric($value) ? (float) $value : 0.0;
                if ($v >= 0.90) {
                    $score += 4;
                } elseif ($v >= 0.70) {
                    $score += 2;
                } elseif ($v >= 0.50) {
                    $score += 1;
                }
            }

            if ($flagged && $score < 3) {
                $score = 3;
            }
        }

        $genericFlagged = (bool) ($data['flagged'] ?? false);
        if ($genericFlagged) {
            $flags[] = 'external_flagged';
        }

        $genericSeverity = $data['severity'] ?? null;
        if (is_numeric($genericSeverity)) {
            $sev = (float) $genericSeverity;
            if ($sev >= 0.9) {
                $score += 4;
            } elseif ($sev >= 0.7) {
                $score += 3;
            } elseif ($sev >= 0.5) {
                $score += 2;
            } elseif ($sev >= 0.3) {
                $score += 1;
            }
        }

        $categories = $data['categories'] ?? null;
        if (is_array($categories)) {
            foreach ($categories as $name => $value) {
                if ((bool) $value) {
                    $flags[] = 'ext_' . (string) $name;
                }
            }
        }

        $flags = array_values(array_unique($flags));
        if (empty($flags) && $score === 0) {
            return null;
        }

        return [
            'score' => $score,
            'flags' => $flags,
        ];
    }

    private function extractUrlCount(string $text): int
    {
        $count = 0;

        if (preg_match_all('#https?://[^\s]+#iu', $text, $httpMatches) === false) {
            $httpMatches = [[]];
        }

        if (preg_match_all('/\bwww\.[^\s]+/iu', $text, $wwwMatches) === false) {
            $wwwMatches = [[]];
        }

        $count += count($httpMatches[0]);
        $count += count($wwwMatches[0]);

        return $count;
    }

    /**
     * Normalize text to detect abusive words even when obfuscated.
     */
    private function normalizeForWordMatch(string $text): string
    {
        $t = mb_strtolower($text, 'UTF-8');

        // Remove Arabic diacritics and tatweel.
        $t = preg_replace('/[\x{064B}-\x{065F}\x{0670}\x{0640}]/u', '', $t) ?? $t;

        // Unify Arabic letter forms.
        $map = [
            'أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا',
            'ى' => 'ي', 'ئ' => 'ي', 'ؤ' => 'و',
            'ة' => 'ه',
            'گ' => 'ك', 'ڤ' => 'ف', 'چ' => 'ج', 'پ' => 'ب',
        ];
        $t = strtr($t, $map);

        // Convert common leetspeak and symbol substitutions.
        $leet = [
            '0' => 'o', '1' => 'i', '3' => 'e', '4' => 'a', '5' => 's', '7' => 't',
            '@' => 'a', '$' => 's', '!' => 'i',
        ];
        $t = strtr($t, $leet);

        // Replace punctuation with spaces then collapse repeated chars (e.g. fuuuuuck).
        $t = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $t) ?? $t;
        $t = preg_replace('/(.)\1{2,}/u', '$1$1', $t) ?? $t;
        $t = preg_replace('/\s+/u', ' ', trim($t)) ?? trim($t);

        return $t;
    }

    private function hasSuspiciousShortenerUrl(string $text): bool
    {
        $shorteners = (array) config('moderation.reviews.shortener_domains', []);
        if (empty($shorteners)) {
            return false;
        }

        foreach ($shorteners as $domain) {
            $domain = preg_quote((string) $domain, '#');
            if ($domain === '') {
                continue;
            }

            if (preg_match('#(https?://)?(www\.)?' . $domain . '(/|\b)#iu', $text) === 1) {
                return true;
            }
        }

        return false;
    }
}
