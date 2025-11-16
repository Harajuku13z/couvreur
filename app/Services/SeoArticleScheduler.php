<?php

namespace App\Services;

use App\Models\City;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SeoArticleScheduler
{
    /**
     * Calcule le prochain créneau pour créer un article
     * Répartit les articles sur la journée selon la configuration
     */
    public function getNextScheduledTime(): ?Carbon
    {
        // Récupérer la configuration
        // articles_per_day = nombre d'articles par ville par jour
        $articlesPerDay = (int)Setting::get('seo_automation_articles_per_day', 5);
        $citiesCount = City::where('is_favorite', true)->count();
        
        if ($citiesCount === 0) {
            return null;
        }
        
        // Calculer le nombre total d'articles par jour (articles par ville × nombre de villes)
        $totalArticlesPerDay = $articlesPerDay * $citiesCount;
        
        // Récupérer l'heure de début configurée (par défaut 8h)
        $startTimeStr = Setting::get('seo_automation_time', '08:00');
        $startTimeParts = explode(':', $startTimeStr);
        $startHour = (int)($startTimeParts[0] ?? 8);
        $startMinute = (int)($startTimeParts[1] ?? 0);
        
        // Calculer l'intervalle entre chaque article (en minutes)
        // Répartir sur 12 heures à partir de l'heure de début = 720 minutes
        $workingHours = 12 * 60; // 720 minutes
        $intervalMinutes = max(5, floor($workingHours / $totalArticlesPerDay));
        
        // Calculer l'heure de fin (12h après l'heure de début)
        $endHour = ($startHour + 12) % 24;
        
        // Récupérer le dernier article créé aujourd'hui
        $lastArticle = \App\Models\Article::whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->first();
        
        if ($lastArticle) {
            // Prochain créneau = dernier article + intervalle
            $nextTime = $lastArticle->created_at->copy()->addMinutes($intervalMinutes);
            
            // S'assurer qu'on ne dépasse pas l'heure de fin
            if ($nextTime->hour > $endHour || ($nextTime->hour == $endHour && $nextTime->minute > 0)) {
                // Si on dépasse l'heure de fin, commencer demain à l'heure de début
                $nextTime = Carbon::tomorrow()->setTime($startHour, $startMinute);
            }
            
            // S'assurer qu'on ne commence pas avant l'heure de début
            if ($nextTime->hour < $startHour || ($nextTime->hour == $startHour && $nextTime->minute < $startMinute)) {
                $nextTime->setTime($startHour, $startMinute);
            }
        } else {
            // Premier article de la journée : à l'heure de début configurée
            $nextTime = Carbon::today()->setTime($startHour, $startMinute);
            if ($nextTime->isPast()) {
                // Si l'heure de début est passée, commencer maintenant ou au prochain intervalle
                $nextTime = now();
                // Ajuster pour être aligné sur l'intervalle depuis l'heure de début
                $minutesSinceStart = $nextTime->diffInMinutes(Carbon::today()->setTime($startHour, $startMinute));
                $intervalsPassed = floor($minutesSinceStart / $intervalMinutes);
                $nextTime = Carbon::today()->setTime($startHour, $startMinute)->addMinutes(($intervalsPassed + 1) * $intervalMinutes);
                
                // Si on dépasse l'heure de fin, commencer demain
                if ($nextTime->hour > $endHour || ($nextTime->hour == $endHour && $nextTime->minute > 0)) {
                    $nextTime = Carbon::tomorrow()->setTime($startHour, $startMinute);
                }
            }
        }
        
        return $nextTime;
    }
    
    /**
     * Vérifie si c'est le moment de créer un article
     */
    public function shouldCreateArticle(): bool
    {
        $nextTime = $this->getNextScheduledTime();
        
        if (!$nextTime) {
            return false;
        }
        
        // Vérifier si on ignore le quota (mode test)
        $ignoreQuota = Setting::get('seo_automation_ignore_quota', false);
        $ignoreQuota = filter_var($ignoreQuota, FILTER_VALIDATE_BOOLEAN);
        
        // Vérifier d'abord si on a atteint le quota du jour (sauf si on ignore le quota)
        if (!$ignoreQuota) {
            $articlesPerDay = (int)Setting::get('seo_automation_articles_per_day', 5);
            $citiesCount = City::where('is_favorite', true)->count();
            $totalArticlesPerDay = $articlesPerDay * $citiesCount;
            
            $articlesToday = \App\Models\Article::whereDate('created_at', today())->count();
            
            // Si on a atteint le quota, ne pas créer d'article
            if ($articlesToday >= $totalArticlesPerDay) {
                return false;
            }
        }
        
        $now = now();
        $diffMinutes = abs($now->diffInMinutes($nextTime));
        
        // Si on ignore le quota, permettre la création sans restriction de période ni d'heure
        if ($ignoreQuota) {
            // Vérifier si un article a déjà été créé récemment (dans les 1 minute seulement)
            // pour éviter les doublons si le cron s'exécute plusieurs fois rapidement
            $recentArticle = \App\Models\Article::whereDate('created_at', today())
                ->where('created_at', '>=', now()->subMinute())
                ->exists();
            
            if ($recentArticle) {
                return false; // Un article vient d'être créé il y a moins d'1 minute, attendre un peu
            }
            
            // En mode test (ignore quota), permettre la création si :
            // - On est dans une fenêtre de 6 heures après l'heure prévue (très permissif pour les tests)
            // - Ou si on est proche de l'heure (1 heure avant ou après)
            if ($nextTime->isPast()) {
                return $diffMinutes <= 360; // 6 heures de marge en mode test
            }
            // Permettre aussi si on est proche de l'heure (1 heure avant)
            return $diffMinutes <= 60;
        }
        
        // Vérifier si un article a déjà été créé récemment (dans les 5 dernières minutes)
        // pour éviter les doublons si le cron s'exécute plusieurs fois rapidement
        $recentArticle = \App\Models\Article::whereDate('created_at', today())
            ->where('created_at', '>=', now()->subMinutes(5))
            ->exists();
        
        if ($recentArticle) {
            return false; // Un article vient d'être créé, attendre un peu
        }
        
        // Si on est passé l'heure prévue, permettre la création si :
        // 1. On n'a pas atteint le quota
        // 2. On est dans une fenêtre raisonnable (max 2 heures après l'heure prévue)
        // 3. On est toujours dans la période de travail (12h après le début)
        if ($nextTime->isPast()) {
            // Calculer l'heure de fin de la période de travail
            $startTimeStr = Setting::get('seo_automation_time', '08:00');
            $startTimeParts = explode(':', $startTimeStr);
            $startHour = (int)($startTimeParts[0] ?? 8);
            $startMinute = (int)($startTimeParts[1] ?? 0);
            $endTime = Carbon::today()->setTime($startHour, $startMinute)->addHours(12);
            
            // Récupérer les valeurs si pas déjà fait
            $articlesPerDay = (int)Setting::get('seo_automation_articles_per_day', 5);
            $citiesCount = City::where('is_favorite', true)->count();
            $totalArticlesPerDay = $articlesPerDay * $citiesCount;
            $articlesToday = \App\Models\Article::whereDate('created_at', today())->count();
            
            // Si on est encore dans la période de travail et qu'on n'a pas atteint le quota
            if ($now->isBefore($endTime) && $articlesToday < $totalArticlesPerDay) {
                // Permettre la création si on est dans une fenêtre de 2 heures après l'heure prévue
                return $diffMinutes <= 120; // 2 heures de marge
            }
            
            return false;
        }
        
        // Si on est avant l'heure, vérifier qu'on est proche (15 minutes avant max)
        return $diffMinutes <= 15;
    }
    
    /**
     * Récupère la prochaine ville à traiter (rotation équitable)
     * Choisit la ville qui a le moins d'articles aujourd'hui
     */
    public function getNextCity(): ?City
    {
        $cities = City::where('is_favorite', true)->orderBy('id')->get();
        
        if ($cities->isEmpty()) {
            return null;
        }
        
        // Si une seule ville, la retourner directement
        if ($cities->count() === 1) {
            return $cities->first();
        }
        
        // Compter les articles créés aujourd'hui pour chaque ville
        $articlesCountByCity = \App\Models\Article::whereDate('created_at', today())
            ->whereIn('city_id', $cities->pluck('id'))
            ->selectRaw('city_id, COUNT(*) as count')
            ->groupBy('city_id')
            ->pluck('count', 'city_id')
            ->toArray();
        
        // Récupérer la dernière ville traitée pour la rotation
        $lastArticle = \App\Models\Article::whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->first();
        
        $lastCityId = $lastArticle ? $lastArticle->city_id : null;
        
        // Trouver la ville avec le moins d'articles
        $minCount = PHP_INT_MAX;
        $citiesWithMinCount = [];
        
        foreach ($cities as $city) {
            $count = $articlesCountByCity[$city->id] ?? 0;
            
            if ($count < $minCount) {
                $minCount = $count;
                $citiesWithMinCount = [$city];
            } elseif ($count === $minCount) {
                $citiesWithMinCount[] = $city;
            }
        }
        
        // Si plusieurs villes ont le même nombre minimum, utiliser la rotation
        if (count($citiesWithMinCount) > 1 && $lastCityId) {
            // Trouver l'index de la dernière ville dans la liste des villes favorites
            $lastCityIndex = $cities->search(function($city) use ($lastCityId) {
                return $city->id === $lastCityId;
            });
            
            if ($lastCityIndex !== false) {
                // Trouver la prochaine ville dans la liste des villes avec le minimum
                // qui n'est pas la dernière ville traitée
                $nextCity = null;
                $startIndex = ($lastCityIndex + 1) % $cities->count();
                
                // Chercher la prochaine ville dans l'ordre qui a le minimum
                for ($i = 0; $i < $cities->count(); $i++) {
                    $checkIndex = ($startIndex + $i) % $cities->count();
                    $checkCity = $cities[$checkIndex];
                    
                    if (in_array($checkCity, $citiesWithMinCount) && $checkCity->id !== $lastCityId) {
                        $nextCity = $checkCity;
                        break;
                    }
                }
                
                if ($nextCity) {
                    Log::info('SeoArticleScheduler: Ville sélectionnée par rotation', [
                        'city_id' => $nextCity->id,
                        'city_name' => $nextCity->name,
                        'last_city_id' => $lastCityId,
                        'min_count' => $minCount
                    ]);
                    return $nextCity;
                }
            }
        }
        
        // Si pas de rotation possible ou première exécution, prendre la première ville avec le minimum
        $selectedCity = $citiesWithMinCount[0];
        
        Log::info('SeoArticleScheduler: Ville sélectionnée (minimum d\'articles)', [
            'city_id' => $selectedCity->id,
            'city_name' => $selectedCity->name,
            'articles_count' => $minCount,
            'total_cities_with_min' => count($citiesWithMinCount)
        ]);
        
        return $selectedCity;
    }
    
    /**
     * Récupère un mot-clé aléatoire depuis la liste
     */
    public function getRandomKeyword(): ?string
    {
        try {
            $customKeywordsData = Setting::get('seo_custom_keywords', '[]');
            
            // Protection robuste : vérifier le type AVANT toute opération
            $customKeywords = [];
            
            if (is_array($customKeywordsData)) {
                // Si c'est déjà un array, l'utiliser directement
                $customKeywords = $customKeywordsData;
            } elseif (is_string($customKeywordsData)) {
                // Si c'est une string, essayer de le décoder en JSON
                // Protection supplémentaire : vérifier que ce n'est pas déjà un array encodé
                $decoded = json_decode($customKeywordsData, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $customKeywords = $decoded;
                } else {
                    // Si le décodage échoue, essayer de traiter comme une liste séparée par virgules
                    $customKeywords = array_filter(array_map('trim', explode(',', $customKeywordsData)));
                }
            } else {
                // Type inattendu, logger et retourner null
                Log::warning('SeoArticleScheduler: Type de données inattendu pour seo_custom_keywords', [
                    'data_type' => gettype($customKeywordsData),
                    'value' => is_scalar($customKeywordsData) ? $customKeywordsData : 'non-scalar'
                ]);
                return null;
            }
            
            // Vérifier que nous avons un array valide
            if (!is_array($customKeywords)) {
                Log::warning('SeoArticleScheduler: customKeywords n\'est pas un array après traitement', [
                    'data_type' => gettype($customKeywords)
                ]);
                return null;
            }
            
            // Filtrer les mots-clés vides
            $customKeywords = array_filter($customKeywords, function($keyword) {
                return !empty(trim($keyword));
            });
            
            if (empty($customKeywords)) {
                Log::warning('SeoArticleScheduler: Tous les mots-clés sont vides après filtrage');
                return null;
            }
            
            // Réindexer le array après filtrage
            $customKeywords = array_values($customKeywords);
            
            // Retourner un mot-clé aléatoire
            return $customKeywords[array_rand($customKeywords)];
            
        } catch (\Exception $e) {
            Log::error('SeoArticleScheduler: Exception lors de la récupération du mot-clé', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
    
    /**
     * Récupère les statistiques de planification
     */
    public function getScheduleStats(): array
    {
        $articlesPerDay = (int)Setting::get('seo_automation_articles_per_day', 5);
        $citiesCount = City::where('is_favorite', true)->count();
        $totalArticlesPerDay = $articlesPerDay * $citiesCount;
        
        $articlesToday = \App\Models\Article::whereDate('created_at', today())->count();
        
        // Récupérer l'heure de début configurée
        $startTimeStr = Setting::get('seo_automation_time', '08:00');
        $startTimeParts = explode(':', $startTimeStr);
        $startHour = (int)($startTimeParts[0] ?? 8);
        $startMinute = (int)($startTimeParts[1] ?? 0);
        
        $workingHours = 12 * 60; // 720 minutes
        $intervalMinutes = $totalArticlesPerDay > 0 ? max(5, floor($workingHours / $totalArticlesPerDay)) : 0;
        
        $nextTime = $this->getNextScheduledTime();
        
        return [
            'articles_per_day' => $articlesPerDay,
            'cities_count' => $citiesCount,
            'total_articles_per_day' => $totalArticlesPerDay,
            'articles_today' => $articlesToday,
            'remaining_today' => max(0, $totalArticlesPerDay - $articlesToday),
            'interval_minutes' => $intervalMinutes,
            'next_scheduled_time' => $nextTime ? $nextTime->format('H:i') : null,
            'should_create_now' => $this->shouldCreateArticle(),
            'start_time' => sprintf('%02d:%02d', $startHour, $startMinute),
        ];
    }
    
    /**
     * Génère la liste complète des horaires planifiés pour aujourd'hui avec les villes associées
     */
    public function getScheduledTimes(): array
    {
        $articlesPerDay = (int)Setting::get('seo_automation_articles_per_day', 5);
        $cities = City::where('is_favorite', true)->orderBy('id')->get();
        
        if ($cities->isEmpty()) {
            return [];
        }
        
        $citiesCount = $cities->count();
        $totalArticlesPerDay = $articlesPerDay * $citiesCount;
        
        // Récupérer l'heure de début configurée
        $startTimeStr = Setting::get('seo_automation_time', '08:00');
        $startTimeParts = explode(':', $startTimeStr);
        $startHour = (int)($startTimeParts[0] ?? 8);
        $startMinute = (int)($startTimeParts[1] ?? 0);
        
        // Calculer l'intervalle entre chaque article
        $workingHours = 12 * 60; // 720 minutes
        $intervalMinutes = max(5, floor($workingHours / $totalArticlesPerDay));
        
        // Récupérer le dernier article créé aujourd'hui pour déterminer la rotation
        $lastArticle = \App\Models\Article::whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->first();
        
        // Déterminer l'index de départ pour la rotation
        $startCityIndex = 0;
        if ($lastArticle && $lastArticle->city_id) {
            $lastCityIndex = $cities->search(function($city) use ($lastArticle) {
                return $city->id === $lastArticle->city_id;
            });
            if ($lastCityIndex !== false) {
                // Commencer à la ville suivante
                $startCityIndex = ($lastCityIndex + 1) % $citiesCount;
            }
        }
        
        // Générer tous les horaires avec les villes associées
        $scheduledTimes = [];
        $currentTime = Carbon::today()->setTime($startHour, $startMinute);
        
        for ($i = 0; $i < $totalArticlesPerDay; $i++) {
            // Calculer quelle ville sera utilisée (rotation)
            $cityIndex = ($startCityIndex + $i) % $citiesCount;
            $city = $cities[$cityIndex];
            
            // Vérifier si un article a déjà été créé à cette heure pour cette ville
            $articleCreated = \App\Models\Article::where('city_id', $city->id)
                ->whereDate('created_at', today())
                ->whereTime('created_at', '>=', $currentTime->copy()->subMinutes(5))
                ->whereTime('created_at', '<=', $currentTime->copy()->addMinutes(5))
                ->exists();
            
            $scheduledTimes[] = [
                'time' => $currentTime->format('H:i'),
                'datetime' => $currentTime->copy(),
                'article_number' => $i + 1,
                'is_past' => $currentTime->isPast(),
                'city' => [
                    'id' => $city->id,
                    'name' => $city->name,
                    'postal_code' => $city->postal_code,
                ],
                'city_index' => $cityIndex + 1,
                'article_created' => $articleCreated,
            ];
            $currentTime->addMinutes($intervalMinutes);
        }
        
        return $scheduledTimes;
    }
}

