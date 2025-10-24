<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\Review;

class AdPublicController extends Controller
{
    public function index()
    {
        $ads = Ad::where('status', 'published')
            ->with('city')
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->paginate(12);
        
        return view('ads.index', compact('ads'));
    }

    public function show(string $slug)
    {
        $ad = Ad::where('slug', $slug)->where('status', 'published')->firstOrFail();

        return view('ads.show', [
            'ad' => $ad,
            'city' => $ad->city,
        ]);
    }
}



