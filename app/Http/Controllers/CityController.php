<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CityController extends Controller
{
    public function index(Request $request)
    {
        $query = City::query();
        
        // Filtrage par favoris
        if ($request->has('favorites') && $request->favorites == '1') {
            $query->where('is_favorite', true);
        }
        
        // Filtrage par département
        if ($request->has('department') && $request->department) {
            $query->where('department', $request->department);
        }
        
        // Filtrage par région
        if ($request->has('region') && $request->region) {
            $query->where('region', $request->region);
        }
        
        // Recherche par nom
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $cities = $query->orderBy('name', 'asc')->paginate(50);

        $departments = [
            'Ain','Aisne','Allier','Alpes-de-Haute-Provence','Hautes-Alpes','Alpes-Maritimes','Ardèche','Ardennes','Ariège','Aube','Aude','Aveyron','Bouches-du-Rhône','Calvados','Cantal','Charente','Charente-Maritime','Cher','Corrèze','Corse-du-Sud','Haute-Corse','Côte-d\'Or','Côtes-d\'Armor','Creuse','Dordogne','Doubs','Drôme','Eure','Eure-et-Loir','Finistère','Gard','Haute-Garonne','Gers','Gironde','Hérault','Ille-et-Vilaine','Indre','Indre-et-Loire','Isère','Jura','Landes','Loir-et-Cher','Loire','Haute-Loire','Loire-Atlantique','Loiret','Lot','Lot-et-Garonne','Lozère','Maine-et-Loire','Manche','Marne','Haute-Marne','Mayenne','Meurthe-et-Moselle','Meuse','Morbihan','Moselle','Nièvre','Nord','Oise','Orne','Pas-de-Calais','Puy-de-Dôme','Pyrénées-Atlantiques','Hautes-Pyrénées','Pyrénées-Orientales','Bas-Rhin','Haut-Rhin','Rhône','Haute-Saône','Saône-et-Loire','Sarthe','Savoie','Haute-Savoie','Paris','Seine-Maritime','Seine-et-Marne','Yvelines','Deux-Sèvres','Somme','Tarn','Tarn-et-Garonne','Var','Vaucluse','Vendée','Vienne','Haute-Vienne','Vosges','Yonne','Territoire de Belfort','Essonne','Hauts-de-Seine','Seine-Saint-Denis','Val-de-Marne','Val-d\'Oise','Guadeloupe','Martinique','Guyane','La Réunion','Mayotte'
        ];

        $regions = [
            'Auvergne-Rhône-Alpes','Bourgogne-Franche-Comté','Bretagne','Centre-Val de Loire','Corse','Grand Est','Hauts-de-France','Île-de-France','Normandie','Nouvelle-Aquitaine','Occitanie','Pays de la Loire','Provence-Alpes-Côte d\'Azur','Guadeloupe','Martinique','Guyane','La Réunion','Mayotte'
        ];

        // Compter les favoris
        $favoritesCount = City::where('is_favorite', true)->count();

        // Ajouter le code département sur chaque ville (pour l'affichage "71 - Saône-et-Loire")
        foreach ($cities as $city) {
            $city->department_code = $this->departmentCodeFromName($city->department ?? '');
        }

        // Liste des départements déjà présents en base (distinct)
        $existingDepartments = City::select('department')
            ->selectRaw('COUNT(*) as cities_count')
            ->whereNotNull('department')
            ->groupBy('department')
            ->orderBy('department')
            ->get()
            ->map(function($row) {
                return [
                    'name' => $row->department,
                    'code' => $this->departmentCodeFromName($row->department),
                    'cities_count' => $row->cities_count,
                ];
            });

        // Préparer les départements avec codes pour les listes déroulantes
        $departmentsWithCodes = collect($departments)->map(function($name) {
            return [
                'name' => $name,
                'code' => $this->departmentCodeFromName($name),
            ];
        });

        return view('admin.cities.index', compact('cities','departments','regions','favoritesCount', 'departmentsWithCodes', 'existingDepartments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'postal_code' => 'required|string|max:16',
            'department' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:100',
            'active' => 'boolean',
        ]);
        City::create($data);
        return back()->with('success', 'Ville créée');
    }

    public function update(Request $request, $id)
    {
        $city = City::findOrFail($id);
        $data = $request->validate([
            'name' => 'sometimes|string|max:100',
            'postal_code' => 'sometimes|string|max:16',
            'department' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:100',
            'active' => 'boolean',
            'is_favorite' => 'boolean',
        ]);
        $city->update($data);
        return back()->with('success', 'Ville mise à jour');
    }

    public function destroy($id)
    {
        $city = City::findOrFail($id);
        $city->delete();
        return back()->with('success', 'Ville supprimée');
    }

    public function destroyByDepartment(Request $request)
    {
        $data = $request->validate([
            'department' => 'required|string',
        ]);

        $deptName = $data['department'];
        $count = City::where('department', $deptName)->count();
        City::where('department', $deptName)->delete();

        return back()->with('success', "Toutes les villes du département {$deptName} ({$count}) ont été supprimées.");
    }

    public function destroyAll()
    {
        try {
            // Désactiver temporairement les contraintes de clé étrangère
            \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            
            // Supprimer toutes les villes
            \DB::table('cities')->truncate();
            
            // Réactiver les contraintes de clé étrangère
            \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            
            return back()->with('success', 'Toutes les villes ont été supprimées.');
        } catch (\Exception $e) {
            // En cas d'erreur, utiliser DELETE au lieu de TRUNCATE
            \DB::table('cities')->delete();
            return back()->with('success', 'Toutes les villes ont été supprimées.');
        }
    }

    public function importFromJson(Request $request)
    {
        $data = $request->validate([
            'json' => 'required|string'
        ]);

        $decoded = json_decode($data['json'], true);
        if (!is_array($decoded)) {
            return back()->with('success', 'JSON invalide.');
        }

        $citiesList = $decoded['cities'] ?? (isset($decoded[0]) ? $decoded : []);
        $count = 0;
        foreach ($citiesList as $c) {
            if (!isset($c['name'], $c['postal_code'])) { continue; }
            City::firstOrCreate([
                'name' => trim((string)$c['name']),
                'postal_code' => substr(preg_replace('/[^0-9]/', '', (string)$c['postal_code']), 0, 5),
            ], [
                'department' => $c['department'] ?? null,
                'region' => $c['region'] ?? null,
                'active' => true,
            ]);
            $count++;
        }

        return back()->with('success', "Import JSON effectué ({$count} entrées).");
    }

    // --- Imports ---
    public function importByDepartment(Request $request)
    {
        $data = $request->validate([
            'department' => 'required|string'
        ]);

        $svc = app(\App\Services\CityGeoService::class);
        $result = $svc->byDepartment($data['department']);
        $cities = $result['cities'] ?? [];
        foreach ($cities as $c) {
            City::firstOrCreate([
                'name' => $c['name'],
                'postal_code' => $c['postal_code'],
            ], [
                'department' => $c['department'] ?? null,
                'region' => $c['region'] ?? null,
                'active' => true,
            ]);
        }
        if (isset($result['error'])) {
            return back()->with('error', $result['error']);
        }
        return back()->with('success', 'Import par département effectué (' . count($cities) . ' villes ajoutées/existantes).');
    }

    public function importByRegion(Request $request)
    {
        $data = $request->validate([
            'region' => 'required|string'
        ]);

        $svc = app(\App\Services\CityGeoService::class);
        $result = $svc->byRegion($data['region']);
        $cities = $result['cities'] ?? [];
        foreach ($cities as $c) {
            City::firstOrCreate([
                'name' => $c['name'],
                'postal_code' => $c['postal_code'],
            ], [
                'department' => $c['department'] ?? null,
                'region' => $c['region'] ?? null,
                'active' => true,
            ]);
        }
        if (isset($result['error'])) {
            return back()->with('error', $result['error']);
        }
        return back()->with('success', 'Import par région effectué (' . count($cities) . ' villes ajoutées/existantes).');
    }

    public function importByRadius(Request $request)
    {
        $data = $request->validate([
            'address' => 'required|string',
            'radius_km' => 'required|integer|min:1|max:200',
        ]);

        $svc = app(\App\Services\CityGeoService::class);
        $result = $svc->byRadius($data['address'], (int)$data['radius_km']);
        $cities = $result['cities'] ?? [];
        foreach ($cities as $c) {
            City::firstOrCreate([
                'name' => $c['name'],
                'postal_code' => $c['postal_code'],
            ], [
                'department' => $c['department'] ?? null,
                'region' => $c['region'] ?? null,
                'active' => true,
            ]);
        }
        $msg = isset($result['error']) ? ('Avertissement: ' . $result['error']) : 'Import par rayon effectué (' . count($cities) . ' entrées).';
        return back()->with('success', $msg);
    }

    private function departmentCodeFromName(?string $name): ?string
    {
        if (!$name) return null;
        $map = [
            'Ain' => '01', 'Aisne' => '02', 'Allier' => '03', 'Alpes-de-Haute-Provence' => '04', 'Hautes-Alpes' => '05',
            'Alpes-Maritimes' => '06', 'Ardèche' => '07', 'Ardennes' => '08', 'Ariège' => '09', 'Aube' => '10',
            'Aude' => '11', 'Aveyron' => '12', 'Bouches-du-Rhône' => '13', 'Calvados' => '14', 'Cantal' => '15',
            'Charente' => '16', 'Charente-Maritime' => '17', 'Cher' => '18', 'Corrèze' => '19', 'Corse-du-Sud' => '2A',
            'Haute-Corse' => '2B', 'Côte-d\'Or' => '21', 'Côtes-d\'Armor' => '22', 'Creuse' => '23', 'Dordogne' => '24',
            'Doubs' => '25', 'Drôme' => '26', 'Eure' => '27', 'Eure-et-Loir' => '28', 'Finistère' => '29',
            'Gard' => '30', 'Haute-Garonne' => '31', 'Gers' => '32', 'Gironde' => '33', 'Hérault' => '34',
            'Ille-et-Vilaine' => '35', 'Indre' => '36', 'Indre-et-Loire' => '37', 'Isère' => '38', 'Jura' => '39',
            'Landes' => '40', 'Loir-et-Cher' => '41', 'Loire' => '42', 'Haute-Loire' => '43', 'Loire-Atlantique' => '44',
            'Loiret' => '45', 'Lot' => '46', 'Lot-et-Garonne' => '47', 'Lozère' => '48', 'Maine-et-Loire' => '49',
            'Manche' => '50', 'Marne' => '51', 'Haute-Marne' => '52', 'Mayenne' => '53', 'Meurthe-et-Moselle' => '54',
            'Meuse' => '55', 'Morbihan' => '56', 'Moselle' => '57', 'Nièvre' => '58', 'Nord' => '59',
            'Oise' => '60', 'Orne' => '61', 'Pas-de-Calais' => '62', 'Puy-de-Dôme' => '63', 'Pyrénées-Atlantiques' => '64',
            'Hautes-Pyrénées' => '65', 'Pyrénées-Orientales' => '66', 'Bas-Rhin' => '67', 'Haut-Rhin' => '68',
            'Rhône' => '69', 'Haute-Saône' => '70', 'Saône-et-Loire' => '71', 'Sarthe' => '72', 'Savoie' => '73',
            'Haute-Savoie' => '74', 'Paris' => '75', 'Seine-Maritime' => '76', 'Seine-et-Marne' => '77', 'Yvelines' => '78',
            'Deux-Sèvres' => '79', 'Somme' => '80', 'Tarn' => '81', 'Tarn-et-Garonne' => '82', 'Var' => '83',
            'Vaucluse' => '84', 'Vendée' => '85', 'Vienne' => '86', 'Haute-Vienne' => '87', 'Vosges' => '88',
            'Yonne' => '89', 'Territoire de Belfort' => '90', 'Essonne' => '91', 'Hauts-de-Seine' => '92',
            'Seine-Saint-Denis' => '93', 'Val-de-Marne' => '94', 'Val-d\'Oise' => '95', 'Guadeloupe' => '971',
            'Martinique' => '972', 'Guyane' => '973', 'La Réunion' => '974', 'Mayotte' => '976',
        ];
        return $map[$name] ?? null;
    }

    // --- Fake generators (placeholder for IA/API Geo) ---
    private function fakeCitiesFromDepartment(string $department): array
    {
        // Minimal seed list; to be replaced by IA or geo API
        return [
            ['name' => 'Ville-1 ' . $department, 'postal_code' => '00001', 'department' => $department, 'region' => null],
            ['name' => 'Ville-2 ' . $department, 'postal_code' => '00002', 'department' => $department, 'region' => null],
            ['name' => 'Commune-3 ' . $department, 'postal_code' => '00003', 'department' => $department, 'region' => null],
        ];
    }

    private function fakeCitiesFromRegion(string $region): array
    {
        return [
            ['name' => 'Ville-A ' . $region, 'postal_code' => '10001', 'department' => null, 'region' => $region],
            ['name' => 'Village-B ' . $region, 'postal_code' => '10002', 'department' => null, 'region' => $region],
            ['name' => 'Commune-C ' . $region, 'postal_code' => '10003', 'department' => null, 'region' => $region],
        ];
    }

    private function fakeCitiesFromRadius(string $address, int $radiusKm): array
    {
        return [
            ['name' => 'Centre ' . Str::limit($address, 10, ''), 'postal_code' => '20001', 'department' => null, 'region' => null],
            ['name' => 'Périphérie 1', 'postal_code' => '20002', 'department' => null, 'region' => null],
            ['name' => 'Périphérie 2', 'postal_code' => '20003', 'department' => null, 'region' => null],
        ];
    }

    /**
     * Toggle favorite status for a city
     */
    public function toggleFavorite($id)
    {
        try {
            $city = City::findOrFail($id);
            $city->is_favorite = !$city->is_favorite;
            $city->save();
            
            $favoritesCount = City::where('is_favorite', true)->count();
            
            return response()->json([
                'success' => true,
                'is_favorite' => $city->is_favorite,
                'favorites_count' => $favoritesCount,
                'message' => $city->is_favorite ? 'Ville ajoutée aux favoris' : 'Ville retirée des favoris'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cities filtered by AJAX
     */
    public function getCities(Request $request)
    {
        $query = City::query();
        
        // Filtrage par favoris
        if ($request->has('favorites') && $request->favorites == '1') {
            $query->where('is_favorite', true);
        }
        
        // Filtrage par département
        if ($request->has('department') && $request->department) {
            $query->where('department', $request->department);
        }
        
        // Filtrage par région
        if ($request->has('region') && $request->region) {
            $query->where('region', $request->region);
        }
        
        // Recherche par nom
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $cities = $query->orderBy('name', 'asc')->limit(100)->get();
        
        return response()->json([
            'success' => true,
            'cities' => $cities,
            'count' => $cities->count()
        ]);
    }

    /**
     * Get departments for a region
     */
    public function getDepartments(Request $request)
    {
        $region = $request->get('region');
        
        if (!$region) {
            return response()->json(['departments' => []]);
        }
        
        $departments = City::where('region', $region)
            ->distinct()
            ->pluck('department')
            ->filter()
            ->sort()
            ->values();
            
        return response()->json(['departments' => $departments]);
    }
}


