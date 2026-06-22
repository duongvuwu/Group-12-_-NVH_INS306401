<?php
declare(strict_types=1);

class AssistantIntentResolver
{
    public function resolve(string $question, array $context = []): array
    {
        $normalized = $this->normalize($question);
        $lastIntent = (string)($context['intent'] ?? '');

        if ($normalized === '' || preg_match('/^(xin chao|chao|hello|hi|help|tro giup)$/', $normalized)) {
            return ['intent' => 'help', 'params' => []];
        }

        if ($lastIntent === 'expiring_licenses'
            && preg_match('/^(?:trong )?(\d{1,3})(?: ngay)?$/', $normalized, $match)) {
            return [
                'intent' => 'expiring_licenses',
                'params' => ['days' => $this->boundedNumber((int)$match[1], 1, 365)],
            ];
        }

        if ($this->containsAny($normalized, ['qua han', 'chua thu hoi', 'overdue', 'expired but active'])) {
            return ['intent' => 'overdue_unrevoked', 'params' => []];
        }

        if ($this->containsAny($normalized, ['sap het han', 'het han trong', 'expir', 'den han'])) {
            return [
                'intent' => 'expiring_licenses',
                'params' => ['days' => $this->extractNumber($normalized, 14, 1, 365)],
            ];
        }

        if ($this->containsAny($normalized, ['can chu y', 'rui ro', 'canh bao', 'tinh hinh he thong', 'health check'])) {
            return ['intent' => 'risk_summary', 'params' => []];
        }

        if ($this->containsAny($normalized, ['sap het key', 'it key', 'key con trong', 'low inventory', 'low on keys', 'running out of keys', 'available keys', 'con bao nhieu key'])) {
            return [
                'intent' => 'low_inventory',
                'params' => ['threshold' => $this->extractNumber($normalized, 5, 0, 1000)],
            ];
        }

        if (($this->containsAny($normalized, ['khoa', 'department']))
            && $this->containsAny($normalized, ['nhieu nhat', 'top', 'su dung nhieu', 'most licenses', 'highest usage', 'uses the most'])) {
            return ['intent' => 'top_departments', 'params' => ['limit' => 5]];
        }

        if ($this->containsAny($normalized, ['license cua', 'license cho', 'licenses for', 'license for', 'licenses of', 'user ', 'nguoi dung', 'sinh vien', 'giang vien'])) {
            return [
                'intent' => 'user_licenses',
                'params' => ['search' => $this->extractUserSearch($question, $normalized)],
            ];
        }

        if ($this->containsAny($normalized, ['bao nhieu license', 'how many licenses', 'active licenses', 'license active', 'dang active', 'dang hoat dong', 'dang dung'])) {
            return ['intent' => 'active_license_count', 'params' => []];
        }

        return ['intent' => 'no_match', 'params' => []];
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');
        $value = strtr($value, [
            'à'=>'a','á'=>'a','ạ'=>'a','ả'=>'a','ã'=>'a','â'=>'a','ầ'=>'a','ấ'=>'a','ậ'=>'a','ẩ'=>'a','ẫ'=>'a','ă'=>'a','ằ'=>'a','ắ'=>'a','ặ'=>'a','ẳ'=>'a','ẵ'=>'a',
            'è'=>'e','é'=>'e','ẹ'=>'e','ẻ'=>'e','ẽ'=>'e','ê'=>'e','ề'=>'e','ế'=>'e','ệ'=>'e','ể'=>'e','ễ'=>'e',
            'ì'=>'i','í'=>'i','ị'=>'i','ỉ'=>'i','ĩ'=>'i',
            'ò'=>'o','ó'=>'o','ọ'=>'o','ỏ'=>'o','õ'=>'o','ô'=>'o','ồ'=>'o','ố'=>'o','ộ'=>'o','ổ'=>'o','ỗ'=>'o','ơ'=>'o','ờ'=>'o','ớ'=>'o','ợ'=>'o','ở'=>'o','ỡ'=>'o',
            'ù'=>'u','ú'=>'u','ụ'=>'u','ủ'=>'u','ũ'=>'u','ư'=>'u','ừ'=>'u','ứ'=>'u','ự'=>'u','ử'=>'u','ữ'=>'u',
            'ỳ'=>'y','ý'=>'y','ỵ'=>'y','ỷ'=>'y','ỹ'=>'y','đ'=>'d',
        ]);
        $value = preg_replace('/[^a-z0-9@._\-\s]/', ' ', $value) ?? '';

        return trim(preg_replace('/\s+/', ' ', $value) ?? '');
    }

    private function containsAny(string $value, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($value, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function extractNumber(string $value, int $default, int $minimum, int $maximum): int
    {
        if (!preg_match('/\b(\d{1,4})\b/', $value, $match)) {
            return $default;
        }

        return $this->boundedNumber((int)$match[1], $minimum, $maximum);
    }

    private function boundedNumber(int $value, int $minimum, int $maximum): int
    {
        return max($minimum, min($maximum, $value));
    }

    private function extractUserSearch(string $original, string $normalized): string
    {
        if (preg_match('/[a-z0-9._-]+@vnu\.edu\.vn/i', $original, $email)) {
            return strtolower($email[0]);
        }

        $patterns = [
            '/license (?:cua|cho) (.+?)(?: dang| co|$)/',
            '/licenses? (?:for|of) (.+?)(?: is| has|$)/',
            '/(?:nguoi dung|sinh vien|giang vien) (.+?)(?: dang| co|$)/',
            '/user (.+?)(?: is| has|$)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $normalized, $match)) {
                return trim($match[1]);
            }
        }

        return '';
    }
}
