<?php

namespace App\Http\Controllers;

use App\Services\SitemapService;

class SitemapController extends Controller
{
    public function index()
    {
        $indexPath = public_path('sitemap.xml');

        if (!file_exists($indexPath)) {
            $result = app(SitemapService::class)->generateSitemap();
            if (!$result['success'] || !file_exists($indexPath)) {
                abort(500, 'Sitemap generation failed');
            }
        }

        return response(file_get_contents($indexPath), 200)
            ->header('Content-Type', 'application/xml');
    }
}
