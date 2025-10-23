<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CityGeoService
{
    public function byDepartment(string $departmentInput): array
    {
        $code = $this->extractDepartmentCode($departmentInput);
        if (!$code) return ['error' => 'Code département invalide'];

        $resp = Http::timeout(30)->get('https://geo.api.gouv.fr/communes', [
            'codeDepartement' => $code,
            'fields' => 'nom,codesPostaux,codeDepartement,codeRegion,centre',
            'format' => 'json',
            'geometry' => 'centre',
        ]);

        if (!$resp->ok()) return ['error' => 'geo.api.gouv.fr indisponible'];
        return ['cities' => $this->mapCommunes($resp->json())];
    }

    public function byRegion(string $regionInput): array
    {
        $code = $this->regionCodeFromName($regionInput);
        if (!$code) return ['error' => 'Région inconnue'];

        $resp = Http::timeout(30)->get('https://geo.api.gouv.fr/communes', [
            'codeRegion' => $code,
            'fields' => 'nom,codesPostaux,codeDepartement,codeRegion,centre',
            'format' => 'json',
            'geometry' => 'centre',
        ]);
        if (!$resp->ok()) return ['error' => 'geo.api.gouv.fr indisponible'];
        return ['cities' => $this->mapCommunes($resp->json())];
    }

    public function byRadius(string $address, int $radiusKm): array
    {
        // Geocode
        $geo = Http::timeout(20)->get('https://api-adresse.data.gouv.fr/search/', [
            'q' => $address,
            'limit' => 1,
        ]);
        if (!$geo->ok() || empty($geo['features'][0])) return ['error' => 'Adresse introuvable'];
        $coords = $geo['features'][0]['geometry']['coordinates'] ?? null; // [lon, lat]
        $context = $geo['features'][0]['properties']['context'] ?? '';
        if (!$coords) return ['error' => 'Coordonnées indisponibles'];
        $lon = (float)$coords[0];
        $lat = (float)$coords[1];

        // Try to get department code from context: "21, Côte-d'Or, Bourgogne-Franche-Comté"
        $deptCode = null;
        if (preg_match('/^(\d{2}|\d{3}|2A|2B)/u', $context, $m)) {
            $deptCode = $m[1];
        }

        // Fetch communes of department (fallback to region if needed) then filter by distance
        $pool = [];
        if ($deptCode) {
            $deptResp = Http::timeout(30)->get('https://geo.api.gouv.fr/communes', [
                'codeDepartement' => $deptCode,
                'fields' => 'nom,codesPostaux,codeDepartement,codeRegion,centre',
                'format' => 'json',
                'geometry' => 'centre',
            ]);
            if ($deptResp->ok()) $pool = $deptResp->json();
        }

        if (empty($pool)) {
            // Fallback: nearest communes around lat/lon is not provided as list, so take region from reverse context is hard; return nearest few
            $near = Http::timeout(20)->get('https://geo.api.gouv.fr/communes', [
                'lat' => $lat,
                'lon' => $lon,
                'fields' => 'nom,codesPostaux,codeDepartement,codeRegion,centre',
                'format' => 'json',
                'geometry' => 'centre',
            ]);
            if ($near->ok()) $pool = $near->json();
        }

        $filtered = [];
        foreach ($pool as $c) {
            $clat = data_get($c, 'centre.coordinates.1');
            $clon = data_get($c, 'centre.coordinates.0');
            if ($clat === null || $clon === null) continue;
            $d = $this->distanceKm($lat, $lon, (float)$clat, (float)$clon);
            if ($d <= $radiusKm) $filtered[] = $c;
        }

        return ['cities' => $this->mapCommunes($filtered)];
    }

    private function extractDepartmentCode(string $input): ?string
    {
        $input = trim($input);
        
        // Si c'est déjà un code numérique, le retourner
        if (preg_match('/^(2A|2B|\d{2,3})/u', $input, $m)) return $m[1];
        
        // Si c'est un nom de département, le convertir en code
        $departments = [
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
            'Martinique' => '972', 'Guyane' => '973', 'La Réunion' => '974', 'Mayotte' => '976'
        ];
        
        return $departments[$input] ?? null;
    }

    private function regionCodeFromName(string $name): ?string
    {
        $map = [
            'Guadeloupe' => '01', 'Martinique' => '02', 'Guyane' => '03', 'La Réunion' => '04', 'Mayotte' => '06',
            'Île-de-France' => '11', 'Centre-Val de Loire' => '24', 'Bourgogne-Franche-Comté' => '27', 'Normandie' => '28',
            'Hauts-de-France' => '32', 'Grand Est' => '44', 'Pays de la Loire' => '52', 'Bretagne' => '53',
            'Nouvelle-Aquitaine' => '75', 'Occitanie' => '76', 'Auvergne-Rhône-Alpes' => '84', 'Provence-Alpes-Côte d\'Azur' => '93', 'Corse' => '94',
        ];
        return $map[$name] ?? null;
    }

    private function mapCommunes(array $list): array
    {
        $out = [];
        foreach ($list as $c) {
            $codes = $c['codesPostaux'] ?? [];
            if (empty($codes)) continue;
            foreach ($codes as $cp) {
                $out[] = [
                    'name' => $c['nom'],
                    'postal_code' => $cp,
                    'department' => $this->getDepartmentName($c['codeDepartement'] ?? ''),
                    'region' => $this->getRegionName($c['codeRegion'] ?? ''),
                ];
            }
        }
        return $out;
    }

    private function distanceKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $R * $c;
    }

    private function getDepartmentName(string $code): string
    {
        $departments = [
            '01' => 'Ain', '02' => 'Aisne', '03' => 'Allier', '04' => 'Alpes-de-Haute-Provence', '05' => 'Hautes-Alpes',
            '06' => 'Alpes-Maritimes', '07' => 'Ardèche', '08' => 'Ardennes', '09' => 'Ariège', '10' => 'Aube',
            '11' => 'Aude', '12' => 'Aveyron', '13' => 'Bouches-du-Rhône', '14' => 'Calvados', '15' => 'Cantal',
            '16' => 'Charente', '17' => 'Charente-Maritime', '18' => 'Cher', '19' => 'Corrèze', '2A' => 'Corse-du-Sud',
            '2B' => 'Haute-Corse', '21' => 'Côte-d\'Or', '22' => 'Côtes-d\'Armor', '23' => 'Creuse', '24' => 'Dordogne',
            '25' => 'Doubs', '26' => 'Drôme', '27' => 'Eure', '28' => 'Eure-et-Loir', '29' => 'Finistère',
            '30' => 'Gard', '31' => 'Haute-Garonne', '32' => 'Gers', '33' => 'Gironde', '34' => 'Hérault',
            '35' => 'Ille-et-Vilaine', '36' => 'Indre', '37' => 'Indre-et-Loire', '38' => 'Isère', '39' => 'Jura',
            '40' => 'Landes', '41' => 'Loir-et-Cher', '42' => 'Loire', '43' => 'Haute-Loire', '44' => 'Loire-Atlantique',
            '45' => 'Loiret', '46' => 'Lot', '47' => 'Lot-et-Garonne', '48' => 'Lozère', '49' => 'Maine-et-Loire',
            '50' => 'Manche', '51' => 'Marne', '52' => 'Haute-Marne', '53' => 'Mayenne', '54' => 'Meurthe-et-Moselle',
            '55' => 'Meuse', '56' => 'Morbihan', '57' => 'Moselle', '58' => 'Nièvre', '59' => 'Nord',
            '60' => 'Oise', '61' => 'Orne', '62' => 'Pas-de-Calais', '63' => 'Puy-de-Dôme', '64' => 'Pyrénées-Atlantiques',
            '65' => 'Hautes-Pyrénées', '66' => 'Pyrénées-Orientales', '67' => 'Bas-Rhin', '68' => 'Haut-Rhin',
            '69' => 'Rhône', '70' => 'Haute-Saône', '71' => 'Saône-et-Loire', '72' => 'Sarthe', '73' => 'Savoie',
            '74' => 'Haute-Savoie', '75' => 'Paris', '76' => 'Seine-Maritime', '77' => 'Seine-et-Marne', '78' => 'Yvelines',
            '79' => 'Deux-Sèvres', '80' => 'Somme', '81' => 'Tarn', '82' => 'Tarn-et-Garonne', '83' => 'Var',
            '84' => 'Vaucluse', '85' => 'Vendée', '86' => 'Vienne', '87' => 'Haute-Vienne', '88' => 'Vosges',
            '89' => 'Yonne', '90' => 'Territoire de Belfort', '91' => 'Essonne', '92' => 'Hauts-de-Seine',
            '93' => 'Seine-Saint-Denis', '94' => 'Val-de-Marne', '95' => 'Val-d\'Oise', '971' => 'Guadeloupe',
            '972' => 'Martinique', '973' => 'Guyane', '974' => 'La Réunion', '976' => 'Mayotte'
        ];
        
        return $departments[$code] ?? $code;
    }

    private function getRegionName(string $code): string
    {
        $regions = [
            '01' => 'Guadeloupe', '02' => 'Martinique', '03' => 'Guyane', '04' => 'La Réunion', '06' => 'Mayotte',
            '11' => 'Île-de-France', '24' => 'Centre-Val de Loire', '27' => 'Bourgogne-Franche-Comté', '28' => 'Normandie',
            '32' => 'Hauts-de-France', '44' => 'Grand Est', '52' => 'Pays de la Loire', '53' => 'Bretagne',
            '75' => 'Nouvelle-Aquitaine', '76' => 'Occitanie', '84' => 'Auvergne-Rhône-Alpes', '93' => 'Provence-Alpes-Côte d\'Azur', '94' => 'Corse'
        ];
        
        return $regions[$code] ?? $code;
    }
}



