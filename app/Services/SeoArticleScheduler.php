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
        
        // Récupérer le dernier article créé aujourd'hui (basé sur published_at pour respecter les horaires planifiés)
        $lastArticle = \App\Models\Article::whereDate('published_at', today())
            ->orderBy('published_at', 'desc')
            ->first();
        
        // Calculer l'heure de fin de la période de travail
        $endTime = Carbon::today()->setTime($startHour, $startMinute)->addHours(12);
        $now = now();
        $firstCreneau = Carbon::today()->setTime($startHour, $startMinute);
        
        if ($lastArticle) {
            // ⚠️ CORRECTION : Utiliser published_at au lieu de created_at pour respecter les horaires planifiés
            $lastArticleTime = $lastArticle->published_at ?? $lastArticle->created_at;
            // Prochain créneau = dernier article + intervalle
            $nextTime = $lastArticleTime->copy()->addMinutes($intervalMinutes);
            
            // IMPORTANT: Vérifier qu'au moins l'intervalle minimum s'est écoulé depuis le dernier article
            // pour éviter de créer plusieurs articles trop rapidement
            $minutesSinceLastArticle = $now->diffInMinutes($lastArticleTime);
            
            // Si moins de l'intervalle minimum s'est écoulé, retourner le prochain créneau futur
            if ($minutesSinceLastArticle < $intervalMinutes) {
                // Un article vient d'être créé, retourner le prochain créneau (qui sera dans le futur)
                Log::info('SeoArticleScheduler: Article créé récemment, prochain créneau dans le futur', [
                    'last_article_time' => $lastArticleTime->format('H:i'),
                    'next_time' => $nextTime->format('H:i'),
                    'minutes_since_last' => $minutesSinceLastArticle,
                    'interval_minutes' => $intervalMinutes
                ]);
                return $nextTime;
            }
            
            // Si le créneau calculé est dans le passé ET qu'on est encore dans la période de travail,
            // retourner ce créneau pour permettre la création (mais seulement si l'intervalle minimum est respecté)
            if ($nextTime->isPast() && $nextTime->isToday() && $now->isBefore($endTime)) {
                Log::info('SeoArticleScheduler: Créneau passé détecté (aujourd\'hui)', [
                    'next_time' => $nextTime->format('Y-m-d H:i:s'),
                    'current_time' => $now->format('Y-m-d H:i:s'),
                    'last_article_time' => $lastArticleTime->format('Y-m-d H:i:s'),
                    'minutes_since_last' => $minutesSinceLastArticle,
                    'interval_minutes' => $intervalMinutes
                ]);
                return $nextTime;
            }
            
            // Si le créneau calculé est demain ou après-demain, trouver le dernier créneau manqué aujourd'hui
            if (!$nextTime->isToday() || ($nextTime->isFuture() && $now->isBefore($endTime))) {
                // Calculer tous les créneaux prévus aujourd'hui depuis le début
                $currentCreneau = $firstCreneau->copy();
                $lastMissedCreneau = null;
                
                // Trouver le dernier créneau qui devrait avoir été créé
                while ($currentCreneau->isBefore($endTime) && $currentCreneau->isBefore($now)) {
                    $lastMissedCreneau = $currentCreneau->copy();
                    $currentCreneau->addMinutes($intervalMinutes);
                }
                
                if ($lastMissedCreneau && $now->isBefore($endTime)) {
                    Log::info('SeoArticleScheduler: Créneau manqué trouvé (calcul)', [
                        'creneau' => $lastMissedCreneau->format('Y-m-d H:i:s'),
                        'current_time' => $now->format('Y-m-d H:i:s'),
                        'next_calculated' => $nextTime->format('Y-m-d H:i:s')
                    ]);
                    return $lastMissedCreneau;
                }
            }
            
            // S'assurer qu'on ne dépasse pas l'heure de fin
            if ($nextTime->hour > $endHour || ($nextTime->hour == $endHour && $nextTime->minute > 0)) {
                // Si on dépasse l'heure de fin mais qu'on est encore dans la période de travail,
                // trouver le dernier créneau manqué aujourd'hui
                if ($now->isBefore($endTime)) {
                    $currentCreneau = $firstCreneau->copy();
                    $lastMissedCreneau = null;
                    
                    while ($currentCreneau->isBefore($endTime) && $currentCreneau->isBefore($now)) {
                        $lastMissedCreneau = $currentCreneau->copy();
                        $currentCreneau->addMinutes($intervalMinutes);
                    }
                    
                    if ($lastMissedCreneau) {
                        return $lastMissedCreneau;
                    }
                }
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
        
        // ⚠️ PROTECTION : Vérifier les erreurs ChatGPT récentes (quota manquant)
        // Si une erreur ChatGPT s'est produite dans les 30 dernières minutes, arrêter les tentatives
        $recentChatGptError = \App\Models\SeoAutomation::where('created_at', '>=', now()->subMinutes(30))
            ->where('status', 'failed')
            ->where(function($query) {
                $query->where('error_message', 'like', '%ChatGPT%')
                    ->orWhere('error_message', 'like', '%API IA%')
                    ->orWhere('error_message', 'like', '%quota%')
                    ->orWhere('error_message', 'like', '%rate limit%');
            })
            ->exists();
        
        if ($recentChatGptError) {
            Log::warning('SeoArticleScheduler: Erreur ChatGPT récente détectée, arrêt des tentatives', [
                'next_time' => $nextTime->format('H:i'),
                'current_time' => now()->format('H:i')
            ]);
            return false; // Arrêter les tentatives si erreur ChatGPT récente
        }
        
        // Vérifier si on ignore le quota (mode test)
        $ignoreQuota = Setting::get('seo_automation_ignore_quota', false);
        $ignoreQuota = filter_var($ignoreQuota, FILTER_VALIDATE_BOOLEAN);
        
        // Vérifier d'abord si on a atteint le quota du jour (sauf si on ignore le quota)
        if (!$ignoreQuota) {
            $articlesPerDay = (int)Setting::get('seo_automation_articles_per_day', 5);
            $citiesCount = City::where('is_favorite', true)->count();
            $totalArticlesPerDay = $articlesPerDay * $citiesCount;
            
            $articlesToday = \App\Models\Article::whereDate('published_at', today())->count();
            
            // Si on a atteint le quota, ne pas créer d'article
            if ($articlesToday >= $totalArticlesPerDay) {
                return false;
            }
        }
        
        $now = now();
        $diffMinutes = abs($now->diffInMinutes($nextTime));
        
        // DEBUG: Logger les valeurs pour comprendre
        Log::info('SeoArticleScheduler: shouldCreateArticle - Début vérification', [
            'next_time' => $nextTime->format('H:i'),
            'current_time' => $now->format('H:i'),
            'is_past' => $nextTime->isPast(),
            'diff_minutes' => $diffMinutes,
            'ignore_quota' => $ignoreQuota,
            'recent_chatgpt_error' => $recentChatGptError
        ]);
        
        // Si on ignore le quota, permettre la création sans restriction de période ni d'heure
        if ($ignoreQuota) {
            // Vérifier si un article a déjà été créé récemment (dans les 1 minute seulement)
            // pour éviter les doublons si le cron s'exécute plusieurs fois rapidement
            $recentArticle = \App\Models\Article::whereDate('published_at', today())
                ->where('published_at', '>=', now()->subMinute())
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
        
        // Si on est passé l'heure prévue, permettre la création si :
        // 1. On n'a pas atteint le quota
        // 2. On est dans une fenêtre raisonnable (max 6 heures après l'heure prévue pour gérer les retards de Hostinger)
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
            $articlesToday = \App\Models\Article::whereDate('published_at', today())->count();
            
            Log::info('SeoArticleScheduler: Créneau passé - Vérification conditions', [
                'next_time' => $nextTime->format('H:i'),
                'current_time' => $now->format('H:i'),
                'end_time' => $endTime->format('H:i'),
                'is_before_end' => $now->isBefore($endTime),
                'articles_today' => $articlesToday,
                'total_per_day' => $totalArticlesPerDay,
                'quota_ok' => $articlesToday < $totalArticlesPerDay
            ]);
            
            // Si on est encore dans la période de travail et qu'on n'a pas atteint le quota
            if ($now->isBefore($endTime) && $articlesToday < $totalArticlesPerDay) {
                // Calculer l'intervalle minimum entre les articles
                $articlesPerDay = (int)Setting::get('seo_automation_articles_per_day', 5);
                $citiesCount = City::where('is_favorite', true)->count();
                $totalArticlesPerDay = $articlesPerDay * $citiesCount;
                $workingHours = 12 * 60; // 720 minutes
                $intervalMinutes = max(5, floor($workingHours / $totalArticlesPerDay));
                
                // ⚠️ PROTECTION RENFORCÉE : Vérifier si un article a été créé récemment (dans l'intervalle minimum)
                // Utiliser published_at pour respecter les horaires planifiés
                $recentArticle = \App\Models\Article::whereDate('published_at', today())
                    ->where('published_at', '>=', now()->subMinutes($intervalMinutes))
                    ->exists();
                
                // ⚠️ PROTECTION SUPPLÉMENTAIRE : Vérifier aussi les logs d'échec récents
                // Si un article a échoué récemment (dans les 2 dernières minutes), ne pas réessayer immédiatement
                $recentFailure = \App\Models\SeoAutomation::where('created_at', '>=', now()->subMinutes(2))
                    ->where('status', 'failed')
                    ->exists();
                
                if ($recentArticle || $recentFailure) {
                    Log::info('SeoArticleScheduler: Article créé récemment ou échec récent, skip (intervalle minimum non respecté)', [
                        'next_time' => $nextTime->format('H:i'),
                        'current_time' => $now->format('H:i'),
                        'interval_minutes' => $intervalMinutes,
                        'recent_article' => $recentArticle,
                        'recent_failure' => $recentFailure
                    ]);
                    return false; // Un article vient d'être créé ou a échoué, attendre l'intervalle minimum
                }
                
                // ⚠️ FENÊTRE RÉDUITE : Permettre la création seulement si on est dans une fenêtre de 15 minutes après l'heure prévue
                // (réduit de 6h à 15min pour éviter les créations toutes les 2 minutes)
                $allowed = $diffMinutes <= 15; // 15 minutes de marge maximum
                
                if ($allowed) {
                    Log::info('SeoArticleScheduler: Création autorisée - créneau passé', [
                        'next_time' => $nextTime->format('H:i'),
                        'current_time' => $now->format('H:i'),
                        'diff_minutes' => $diffMinutes,
                        'articles_today' => $articlesToday,
                        'total_per_day' => $totalArticlesPerDay
                    ]);
                } else {
                    Log::info('SeoArticleScheduler: Heure prévue dépassée de plus de 6h', [
                        'next_time' => $nextTime->format('H:i'),
                        'current_time' => $now->format('H:i'),
                        'diff_minutes' => $diffMinutes,
                        'articles_today' => $articlesToday,
                        'total_per_day' => $totalArticlesPerDay
                    ]);
                }
                
                return $allowed;
            }
            
            Log::info('SeoArticleScheduler: Période de travail terminée ou quota atteint', [
                'next_time' => $nextTime->format('H:i'),
                'current_time' => $now->format('H:i'),
                'end_time' => $endTime->format('H:i'),
                'articles_today' => $articlesToday,
                'total_per_day' => $totalArticlesPerDay,
                'is_before_end' => $now->isBefore($endTime)
            ]);
            
            return false;
        }
        
        // Si on est avant l'heure, vérifier qu'on est proche (30 minutes avant max pour gérer les avances)
        // Mais aussi permettre si on est très proche (5 minutes avant)
        if ($diffMinutes <= 5) {
            // Permettre la création 5 minutes avant l'heure prévue
            return true;
        }
        
        return $diffMinutes <= 30;
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
        
        // Compter les articles créés aujourd'hui pour chaque ville (basé sur published_at)
        $articlesCountByCity = \App\Models\Article::whereDate('published_at', today())
            ->whereIn('city_id', $cities->pluck('id'))
            ->selectRaw('city_id, COUNT(*) as count')
            ->groupBy('city_id')
            ->pluck('count', 'city_id')
            ->toArray();
        
        // Récupérer la dernière ville traitée pour la rotation (basé sur published_at)
        $lastArticle = \App\Models\Article::whereDate('published_at', today())
            ->orderBy('published_at', 'desc')
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
        
        // Récupérer le dernier article créé aujourd'hui pour déterminer la rotation (basé sur published_at)
        $lastArticle = \App\Models\Article::whereDate('published_at', today())
            ->orderBy('published_at', 'desc')
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
            
            // Vérifier si un article a déjà été créé à cette heure
            // Fenêtre élargie à ±30 minutes pour gérer les retards de Hostinger
            $windowStart = $currentTime->copy()->subMinutes(30);
            $windowEnd = $currentTime->copy()->addMinutes(30);
            
            // Vérifier d'abord si un article a été créé pour cette ville dans cette fenêtre (basé sur published_at)
            $articleCreatedForCity = \App\Models\Article::where('city_id', $city->id)
                ->whereDate('published_at', today())
                ->where('published_at', '>=', $windowStart)
                ->where('published_at', '<=', $windowEnd)
                ->exists();
            
            // Si pas d'article pour cette ville, vérifier s'il y a un article créé dans cette fenêtre
            // (peut arriver si la rotation a changé ou si le système a créé un article pour une autre ville)
            $articleCreated = $articleCreatedForCity;
            if (!$articleCreated) {
                $articleCreated = \App\Models\Article::whereDate('published_at', today())
                    ->where('published_at', '>=', $windowStart)
                    ->where('published_at', '<=', $windowEnd)
                    ->exists();
            }
            
            // Si pas d'article créé et que c'est dans le passé, vérifier les erreurs
            $errorMessage = null;
            if (!$articleCreated && $currentTime->isPast()) {
                // Vérifier s'il y a des logs d'erreur pour cette ville dans cette fenêtre
                $errorLog = \App\Models\SeoAutomation::where('city_id', $city->id)
                    ->whereDate('created_at', today())
                    ->where('created_at', '>=', $windowStart)
                    ->where('created_at', '<=', $windowEnd)
                    ->where('status', 'failed')
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                if ($errorLog) {
                    $errorMessage = $errorLog->error_message ?? 'Erreur inconnue';
                } else {
                    // Vérifier s'il y a des erreurs générales dans cette fenêtre (peu importe la ville)
                    $generalErrorLog = \App\Models\SeoAutomation::whereDate('created_at', today())
                        ->where('created_at', '>=', $windowStart)
                        ->where('created_at', '<=', $windowEnd)
                        ->where('status', 'failed')
                        ->orderBy('created_at', 'desc')
                        ->first();
                    
                    if ($generalErrorLog) {
                        $errorMessage = $generalErrorLog->error_message ?? 'Erreur inconnue';
                    }
                }
            }
            
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
                'error_message' => $errorMessage,
            ];
            $currentTime->addMinutes($intervalMinutes);
        }
        
        return $scheduledTimes;
    }
}

