<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\City;
use Illuminate\Http\Request;

class ServiceCitiesController extends Controller
{
    /**
     * Afficher la page de génération d'annonces par service et villes
     */
    public function index()
    {
        $services = Service::where('status', 'published')->get();
        $cities = City::orderBy('name')->get();
        
        return view('admin.ads.service-cities', compact('services', 'cities'));
    }
}