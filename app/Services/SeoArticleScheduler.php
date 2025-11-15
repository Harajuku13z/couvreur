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
        
        // Vérifier si un article a déjà été créé récemment (dans les 5 dernières minutes)
        // pour éviter les doublons si le cron s'exécute plusieurs fois rapidement
        $recentArticle = \App\Models\Article::whereDate('created_at', today())
            ->where('created_at', '>=', now()->subMinutes(5))
            ->exists();
        
        if ($recentArticle) {
            return false; // Un article vient d'être créé, attendre un peu
        }
        
        $now = now();
        $diffMinutes = abs($now->diffInMinutes($nextTime));
        
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
            
            // Si on ignore le quota ou si on est encore dans la période de travail et qu'on n'a pas atteint le quota
            if ($ignoreQuota || ($now->isBefore($endTime) && $articlesToday < $totalArticlesPerDay)) {
                // Permettre la création si on est dans une fenêtre de 2 heures après l'heure prévue
                return $diffMinutes <= 120; // 2 heures de marge
            }
            
            return false;
        }
        
        // Si on est avant l'heure, vérifier qu'on est proche (15 minutes avant max)
        return $diffMinutes <= 15;
    }
    
    /**
     * Récupère la prochaine ville à traiter (rotation)
     */
    public function getNextCity(): ?City
    {
        $cities = City::where('is_favorite', true)->orderBy('id')->get();
        
        if ($cities->isEmpty()) {
            return null;
        }
        
        // Récupérer la dernière ville traitée aujourd'hui
        $lastArticle = \App\Models\Article::whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->first();
        
        if ($lastArticle && $lastArticle->city_id) {
            // Trouver l'index de la dernière ville
            $lastCityIndex = $cities->search(function($city) use ($lastArticle) {
                return $city->id === $lastArticle->city_id;
            });
            
            if ($lastCityIndex !== false) {
                // Prendre la ville suivante (rotation)
                $nextIndex = ($lastCityIndex + 1) % $cities->count();
                return $cities[$nextIndex];
            }
        }
        
        // Par défaut, prendre la première ville
        return $cities->first();
    }
    
    /**
     * Récupère un mot-clé aléatoire depuis la liste
     */
    public function getRandomKeyword(): ?string
    {
        $customKeywordsData = Setting::get('seo_custom_keywords', '[]');
        $customKeywords = json_decode($customKeywordsData, true) ?? [];
        
        if (empty($customKeywords) || !is_array($customKeywords)) {
            return null;
        }
        
        // Filtrer les mots-clés vides
        $customKeywords = array_filter($customKeywords, function($keyword) {
            return !empty(trim($keyword));
        });
        
        if (empty($customKeywords)) {
            return null;
        }
        
        // Retourner un mot-clé aléatoire
        return $customKeywords[array_rand($customKeywords)];
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

