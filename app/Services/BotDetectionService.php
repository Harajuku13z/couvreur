<?php

namespace App\Services;

class BotDetectionService
{
    /**
     * Liste complète des bots connus
     * Inclut les bots de recherche, crawlers, scrapers, et outils automatisés
     */
    protected static $botPatterns = [
        // Moteurs de recherche
        'googlebot',
        'bingbot',
        'bingpreview',
        'slurp', // Yahoo
        'duckduckbot',
        'baiduspider',
        'yandexbot',
        'yandex',
        'sogou',
        'exabot',
        'facebot', // Facebook
        'ia_archiver', // Archive.org
        'twitterbot',
        'linkedinbot',
        'applebot',
        'facebookexternalhit',
        'facebookcatalog',
        'rogerbot',
        'dotbot',
        'semrushbot',
        'ahrefsbot',
        'mj12bot',
        'petalbot',
        'applebot',
        
        // Outils et scripts automatisés
        'curl',
        'wget',
        'python',
        'php',
        'java',
        'scrapy',
        'guzzle',
        'httpclient',
        'requests',
        'urllib',
        'postman',
        'insomnia',
        'restclient',
        'apache-httpclient',
        'okhttp',
        'go-http-client',
        'node-fetch',
        'axios',
        
        // Autres crawlers
        'spider',
        'crawler',
        'bot',
        'scraper',
        'scan',
        'monitor',
        'check',
        'test',
        'validator',
        'w3c',
        'uptime',
        'pingdom',
        'newrelic',
        'datadog',
        'sentry',
        
        // Spam et malveillants
        'semalt',
        'dotbot',
        'blexbot',
        
        // SEO tools
        'mj12bot',
        'ahrefs',
        'moz',
        'majestic',
        
        // Headless browsers (peuvent être légitimes mais souvent bots)
        'headless',
        'phantom',
        'puppeteer',
        'playwright',
        'selenium',
        'webdriver',
        
        // Empty user agents
        '',
    ];

    /**
     * Détecter si un user agent est un bot
     * 
     * @param string|null $userAgent
     * @return bool
     */
    public static function isBot(?string $userAgent): bool
    {
        // User agent vide = bot probable
        if (empty($userAgent)) {
            return true;
        }

        $userAgentLower = strtolower(trim($userAgent));
        
        // Vérifier chaque pattern
        foreach (self::$botPatterns as $pattern) {
            if (str_contains($userAgentLower, strtolower($pattern))) {
                // Exceptions : certains patterns peuvent être dans des user agents légitimes
                // Par exemple, "bot" peut être dans "robot" mais on veut détecter "bot"
                if ($pattern === 'bot') {
                    // Vérifier que ce n'est pas un mot complet (comme "robot")
                    if (preg_match('/\bbot\b/i', $userAgentLower)) {
                        return true;
                    }
                } else {
                    return true;
                }
            }
        }

        // Vérifications supplémentaires
        // User agents très courts (< 10 caractères) sont suspects
        if (strlen($userAgent) < 10) {
            return true;
        }

        // User agents avec seulement des caractères spéciaux ou numériques
        if (preg_match('/^[^a-z]*$/i', $userAgent)) {
            return true;
        }

        return false;
    }

    /**
     * Obtenir la liste des patterns de bots (pour debug)
     * 
     * @return array
     */
    public static function getBotPatterns(): array
    {
        return self::$botPatterns;
    }

    /**
     * Vérifier si une IP est connue comme bot (Googlebot, etc.)
     * Note: Cette méthode nécessiterait une vérification DNS inversée
     * pour être vraiment efficace, mais on peut faire des vérifications basiques
     * 
     * @param string $ipAddress
     * @return bool
     */
    public static function isBotIp(string $ipAddress): bool
    {
        // Plages IP connues de bots (incomplet, mais utile)
        // Googlebot IPs (exemple, liste incomplète)
        $knownBotRanges = [
            // Googlebot (exemples de plages - liste très incomplète)
            '66.249.',
            '64.233.',
            '72.14.',
            '74.125.',
        ];

        foreach ($knownBotRanges as $range) {
            if (str_starts_with($ipAddress, $range)) {
                // Ici, il faudrait faire une vérification DNS inversée
                // pour confirmer que c'est bien Googlebot
                // Pour l'instant, on retourne false pour ne pas bloquer
                // les vrais utilisateurs qui pourraient être dans ces plages
                return false;
            }
        }

        return false;
    }
}

