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
     * Liste des navigateurs légitimes à toujours autoriser
     * Ces patterns indiquent un navigateur réel, même s'ils contiennent certains mots-clés
     */
    protected static $legitimateBrowsers = [
        'mozilla',
        'chrome',
        'safari',
        'firefox',
        'edge',
        'opera',
        'webkit',
        'gecko',
        'msie',
        'trident',
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
        
        // D'ABORD : Vérifier si c'est un navigateur légitime
        // Si le user agent contient des signes clairs d'un navigateur réel, ce n'est probablement pas un bot
        foreach (self::$legitimateBrowsers as $browser) {
            if (str_contains($userAgentLower, $browser)) {
                // C'est un navigateur légitime, mais vérifier quand même certains patterns évidents de bots
                // Par exemple, "googlebot" même avec "mozilla" reste un bot
                if (preg_match('/googlebot|bingbot|slurp|baiduspider|yandexbot|duckduckbot|facebookexternalhit|twitterbot|linkedinbot/i', $userAgentLower)) {
                    return true; // Bot même avec navigateur
                }
                // Sinon, c'est probablement un vrai navigateur
                return false;
            }
        }
        
        // Patterns de bots évidents (à vérifier avant les patterns moins certains)
        $obviousBots = [
            'googlebot',
            'bingbot',
            'slurp',
            'baiduspider',
            'yandexbot',
            'duckduckbot',
            'facebookexternalhit',
            'twitterbot',
            'linkedinbot',
            'applebot',
            'semrushbot',
            'ahrefsbot',
            'mj12bot',
            'curl',
            'wget',
            'python-requests',
            'scrapy',
            'puppeteer',
            'playwright',
            'selenium',
            'headlesschrome',
            'phantom',
        ];
        
        foreach ($obviousBots as $bot) {
            if (str_contains($userAgentLower, $bot)) {
                return true;
            }
        }
        
        // Vérifier les autres patterns (mais être plus prudent)
        $suspiciousPatterns = [
            'bot',
            'crawler',
            'spider',
            'scraper',
        ];
        
        foreach ($suspiciousPatterns as $pattern) {
            if (str_contains($userAgentLower, $pattern)) {
                // Vérifier que c'est bien le mot entier
                if (preg_match('/\b' . preg_quote($pattern, '/') . '\b/i', $userAgentLower)) {
                    return true;
                }
            }
        }

        // Patterns moins certains - seulement si le user agent est vraiment suspect
        // Ne pas bloquer sur "test", "check", "monitor" s'ils sont dans un contexte de navigateur
        $weakPatterns = ['test', 'check', 'monitor', 'validator'];
        foreach ($weakPatterns as $pattern) {
            // Seulement si le pattern est seul ou dans un contexte vraiment suspect
            if (preg_match('/^' . preg_quote($pattern, '/') . '\/?[\d\.]*$/i', $userAgentLower)) {
                return true;
            }
        }

        // Vérifications supplémentaires
        // User agents très courts (< 5 caractères) sont suspects
        if (strlen($userAgent) < 5) {
            return true;
        }

        // User agents avec seulement des caractères spéciaux ou numériques (sans lettres)
        if (preg_match('/^[^a-z]*$/i', $userAgent) && strlen($userAgent) > 0) {
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


                    return true;
                }
            }
        }

        // Patterns moins certains - seulement si le user agent est vraiment suspect
        // Ne pas bloquer sur "test", "check", "monitor" s'ils sont dans un contexte de navigateur
        $weakPatterns = ['test', 'check', 'monitor', 'validator'];
        foreach ($weakPatterns as $pattern) {
            // Seulement si le pattern est seul ou dans un contexte vraiment suspect
            if (preg_match('/^' . preg_quote($pattern, '/') . '\/?[\d\.]*$/i', $userAgentLower)) {
                return true;
            }
        }

        // Vérifications supplémentaires
        // User agents très courts (< 5 caractères) sont suspects
        if (strlen($userAgent) < 5) {
            return true;
        }

        // User agents avec seulement des caractères spéciaux ou numériques (sans lettres)
        if (preg_match('/^[^a-z]*$/i', $userAgent) && strlen($userAgent) > 0) {
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


                    return true;
                }
            }
        }

        // Patterns moins certains - seulement si le user agent est vraiment suspect
        // Ne pas bloquer sur "test", "check", "monitor" s'ils sont dans un contexte de navigateur
        $weakPatterns = ['test', 'check', 'monitor', 'validator'];
        foreach ($weakPatterns as $pattern) {
            // Seulement si le pattern est seul ou dans un contexte vraiment suspect
            if (preg_match('/^' . preg_quote($pattern, '/') . '\/?[\d\.]*$/i', $userAgentLower)) {
                return true;
            }
        }

        // Vérifications supplémentaires
        // User agents très courts (< 5 caractères) sont suspects
        if (strlen($userAgent) < 5) {
            return true;
        }

        // User agents avec seulement des caractères spéciaux ou numériques (sans lettres)
        if (preg_match('/^[^a-z]*$/i', $userAgent) && strlen($userAgent) > 0) {
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

