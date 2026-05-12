<?php

namespace App\Console\Commands;

use App\Models\Ad;
use App\Services\AdSeoAuditService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Rapport d'audit SEO des annonces.
 *
 * Interprétation rapide:
 * - score >= 80: page solide, à garder indexable
 * - score 60-79: page indexable mais à enrichir
 * - score < 60: réécriture prioritaire ou exclusion de l'index/sitemap
 * - duplicate_fingerprint_groups > 0: revoir les contenus trop proches, fusionner ou différencier
 *
 * Usage:
 * - php artisan seo:audit-ads --sample=500
 * - php artisan seo:audit-ads --all
 */
class AuditAdsSeo extends Command
{
    protected $signature = 'seo:audit-ads {--sample=500 : Nombre d\'annonces à auditer} {--all : Auditer toutes les annonces publiées}';
    protected $description = 'Auditer la qualité SEO des annonces et produire un rapport exploitable';

    public function handle(AdSeoAuditService $auditService): int
    {
        $sample = max(1, (int) $this->option('sample'));
        try {
            $query = Ad::query()
                ->with(['city:id,name,postal_code', 'template:id,service_name,service_slug'])
                ->where('status', 'published')
                ->orderByDesc('updated_at');

            if (!$this->option('all')) {
                $query->limit($sample);
            }

            $ads = $query->get();
        } catch (\Throwable $e) {
            $this->error('Impossible de charger les annonces: ' . $e->getMessage());
            return 1;
        }

        if ($ads->isEmpty()) {
            $this->warn('Aucune annonce publiée à auditer.');
            return 0;
        }

        $rows = [];
        $fingerprints = [];
        $indexableCount = 0;

        foreach ($ads as $ad) {
            $analysis = $auditService->analyze($ad);
            $fingerprints[$analysis['fingerprint']][] = $ad->id;

            if ($analysis['is_indexable']) {
                $indexableCount++;
            }

            $rows[] = [
                'id' => $ad->id,
                'slug' => $ad->slug,
                'title' => $ad->title,
                'city' => $ad->city->name ?? null,
                'postal_code' => $ad->city->postal_code ?? null,
                'service' => $ad->template->service_name ?? $ad->keyword,
                'score' => $analysis['score'],
                'is_indexable' => $analysis['is_indexable'],
                'word_count' => $analysis['metrics']['word_count'],
                'meta_title_length' => $analysis['metrics']['meta_title_length'],
                'meta_description_length' => $analysis['metrics']['meta_description_length'],
                'issues' => $analysis['issues'],
                'url' => route('ads.show', $ad->slug),
                'fingerprint' => $analysis['fingerprint'],
            ];
        }

        $duplicateFingerprints = collect($fingerprints)->filter(fn ($ids) => count($ids) > 1);
        $thinCount = collect($rows)->where('word_count', '<', 350)->count();

        $report = [
            'generated_at' => now()->toIso8601String(),
            'scope' => $this->option('all') ? 'all_published_ads' : 'sample',
            'audited_ads' => count($rows),
            'indexable_ads' => $indexableCount,
            'non_indexable_ads' => count($rows) - $indexableCount,
            'thin_content_ads' => $thinCount,
            'duplicate_fingerprint_groups' => $duplicateFingerprints->count(),
            'rows' => $rows,
        ];

        $dir = 'seo-audits';
        $timestamp = now()->format('Ymd-His');
        $jsonPath = "{$dir}/ads-audit-{$timestamp}.json";
        $csvPath = "{$dir}/ads-audit-{$timestamp}.csv";

        Storage::disk('local')->put($jsonPath, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $csvLines = [
            'id,slug,title,city,postal_code,service,score,is_indexable,word_count,meta_title_length,meta_description_length,url,issues',
        ];

        foreach ($rows as $row) {
            $csvLines[] = implode(',', [
                $row['id'],
                $this->csv($row['slug']),
                $this->csv($row['title']),
                $this->csv($row['city']),
                $this->csv($row['postal_code']),
                $this->csv($row['service']),
                $row['score'],
                $row['is_indexable'] ? '1' : '0',
                $row['word_count'],
                $row['meta_title_length'],
                $row['meta_description_length'],
                $this->csv($row['url']),
                $this->csv(implode(' | ', $row['issues'])),
            ]);
        }

        Storage::disk('local')->put($csvPath, implode("\n", $csvLines) . "\n");

        $this->info('Audit SEO des annonces terminé.');
        $this->line("Annonces auditées : " . count($rows));
        $this->line("Indexables : {$indexableCount}");
        $this->line("Thin content : {$thinCount}");
        $this->line("Groupes suspects de duplication : " . $duplicateFingerprints->count());
        $this->line('Rapport JSON : storage/app/' . $jsonPath);
        $this->line('Rapport CSV : storage/app/' . $csvPath);

        return 0;
    }

    protected function csv($value): string
    {
        $value = (string) ($value ?? '');
        $value = str_replace('"', '""', $value);

        return '"' . $value . '"';
    }
}
