<?php

namespace App\Services;

use App\Models\Ad;
use App\Models\Setting;
use Illuminate\Support\Str;

class AdSeoAuditService
{
    /**
     * Analyse une annonce et retourne un score SEO orienté indexation.
     */
    public function analyze(Ad $ad): array
    {
        $content = (string) ($ad->content_html ?? '');
        $textContent = trim(preg_replace('/\s+/u', ' ', strip_tags($content)));
        $wordCount = str_word_count(Str::ascii($textContent));

        $title = trim((string) ($ad->meta_title ?: $ad->title));
        $description = trim((string) ($ad->meta_description ?? ''));

        $score = 100;
        $issues = [];

        if ($wordCount < 250) {
            $score -= 45;
            $issues[] = 'Contenu très court';
        } elseif ($wordCount < 450) {
            $score -= 25;
            $issues[] = 'Contenu encore léger';
        } elseif ($wordCount < 700) {
            $score -= 10;
            $issues[] = 'Contenu perfectible';
        }

        $h1Count = preg_match_all('/<h1\b/i', $content);
        $h2Count = preg_match_all('/<h2\b/i', $content);
        $internalLinks = preg_match_all('/<a\b[^>]*href=(["\'])(?!https?:\/\/(?!' . preg_quote(parse_url(config('app.url', ''), PHP_URL_HOST) ?: '', '/') . '))/i', $content);
        $images = preg_match_all('/<img\b/i', $content);

        if ($h1Count === 0) {
            $score -= 15;
            $issues[] = 'Aucun H1';
        } elseif ($h1Count > 1) {
            $score -= 8;
            $issues[] = 'Plusieurs H1';
        }

        if ($h2Count === 0) {
            $score -= 10;
            $issues[] = 'Aucun H2';
        }

        if ($internalLinks < 2) {
            $score -= 8;
            $issues[] = 'Maillage interne faible';
        }

        if ($images === 0) {
            $score -= 4;
            $issues[] = 'Aucune image';
        }

        $titleLength = mb_strlen($title);
        if ($titleLength < 35 || $titleLength > 68) {
            $score -= 6;
            $issues[] = 'Meta title hors plage';
        }

        $descriptionLength = mb_strlen($description);
        if ($descriptionLength < 120 || $descriptionLength > 175) {
            $score -= 6;
            $issues[] = 'Meta description hors plage';
        }

        if ($this->containsPlaceholderSignals($content)) {
            $score -= 25;
            $issues[] = 'Placeholders ou texte générique détectés';
        }

        $textFingerprint = $this->fingerprint($textContent);
        $isThinContent = $wordCount < 350;
        $isIndexable = $score >= 60 && !$isThinContent && !$this->containsPlaceholderSignals($content);

        return [
            'score' => max(0, $score),
            'is_indexable' => $isIndexable,
            'is_thin_content' => $isThinContent,
            'issues' => $issues,
            'metrics' => [
                'word_count' => $wordCount,
                'h1_count' => $h1Count,
                'h2_count' => $h2Count,
                'internal_links' => $internalLinks,
                'images' => $images,
                'meta_title_length' => $titleLength,
                'meta_description_length' => $descriptionLength,
            ],
            'fingerprint' => $textFingerprint,
            'recommended_robots' => $isIndexable ? 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' : 'noindex, follow',
        ];
    }

    public function shouldAutoNoindex(): bool
    {
        return filter_var(Setting::get('ads_auto_noindex_low_quality', true), FILTER_VALIDATE_BOOLEAN);
    }

    protected function containsPlaceholderSignals(string $content): bool
    {
        $patterns = [
            '[VILLE]',
            '[CODE_POSTAL]',
            '[RÉGION]',
            '[DÉPARTEMENT]',
            'lorem ipsum',
            'todo',
            'à compléter',
            'contenu en cours de chargement',
        ];

        $lowerContent = Str::lower($content);

        foreach ($patterns as $pattern) {
            if (str_contains($lowerContent, Str::lower($pattern))) {
                return true;
            }
        }

        return false;
    }

    protected function fingerprint(string $text): string
    {
        $normalized = Str::lower(Str::ascii($text));
        $normalized = preg_replace('/[^a-z0-9\s]+/', ' ', $normalized);
        $normalized = preg_replace('/\s+/', ' ', trim($normalized));

        return sha1(Str::limit($normalized, 4000, ''));
    }
}
