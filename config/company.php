<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Informations de l'Entreprise
    |--------------------------------------------------------------------------
    |
    | Ces informations sont utilisées dans toute l'application
    |
    */

    'name' => env('COMPANY_NAME', 'Rénovation Expert'),
    'legal_name' => env('COMPANY_LEGAL_NAME', 'Rénovation Expert SARL'),
    'slogan' => env('COMPANY_SLOGAN', 'Expert en travaux de toiture, façade et isolation'),
    'description' => env('COMPANY_DESCRIPTION', 'Depuis plus de 20 ans, Rénovation Expert accompagne les particuliers et les professionnels dans leurs projets de rénovation. Spécialisés dans les travaux de toiture, façade et isolation, nous mettons notre expertise et notre savoir-faire au service de votre confort et de la valorisation de votre patrimoine.'),
    'short_description' => env('COMPANY_SHORT_DESCRIPTION', 'Entreprise spécialisée en travaux de rénovation : toiture, façade, isolation. Certification RGE, garantie décennale, +20 ans d\'expérience.'),
    
    'phone' => env('COMPANY_PHONE', '01 23 45 67 89'),
    'phone_raw' => env('COMPANY_PHONE_RAW', '0123456789'),
    'email' => env('COMPANY_EMAIL', 'contact@renovation-expert.fr'),
    'website' => env('COMPANY_WEBSITE', 'https://www.renovation-expert.fr'),
    
    'address' => [
        'street' => env('COMPANY_ADDRESS_STREET', '123 Avenue des Travaux'),
        'city' => env('COMPANY_ADDRESS_CITY', 'Paris'),
        'postal_code' => env('COMPANY_ADDRESS_POSTAL_CODE', '75001'),
        'country' => env('COMPANY_ADDRESS_COUNTRY', 'France'),
    ],
    
    'siret' => env('COMPANY_SIRET', '123 456 789 00012'),
    'tva' => env('COMPANY_TVA', 'FR 12 345678901'),
    
    'certifications' => [
        'RGE' => true,
        'Qualibat' => true,
        'Garantie décennale' => true,
    ],
    
    'hours' => [
        'weekdays' => 'Lundi - Vendredi: 8h00 - 18h00',
        'saturday' => 'Samedi: 9h00 - 12h00',
        'sunday' => 'Fermé',
    ],
    
    'social' => [
        'facebook' => env('COMPANY_FACEBOOK', 'https://www.facebook.com/renovationexpert'),
        'instagram' => env('COMPANY_INSTAGRAM', 'https://www.instagram.com/renovationexpert'),
        'linkedin' => env('COMPANY_LINKEDIN', 'https://www.linkedin.com/company/renovationexpert'),
    ],
    
    'stats' => [
        'years_experience' => 20,
        'projects_completed' => 5000,
        'satisfied_customers' => 4800,
        'team_members' => 25,
    ],
    
    'services' => [
        'toiture' => [
            'name' => 'Travaux de Toiture',
            'description' => 'Rénovation, réparation et entretien de tous types de toitures',
            'icon' => 'icons2/Toiture/Toiture.webp',
        ],
        'facade' => [
            'name' => 'Travaux de Façade',
            'description' => 'Ravalement, nettoyage et isolation des façades',
            'icon' => 'icons2/Facade.webp',
        ],
        'isolation' => [
            'name' => 'Isolation Thermique',
            'description' => 'Isolation des combles, murs et sols pour économiser l\'énergie',
            'icon' => 'icons2/Isolation.webp',
        ],
    ],
    
    'google' => [
        'rating' => 4.9,
        'total_reviews' => 156,
        'place_id' => env('GOOGLE_PLACE_ID', ''),
    ],
];


return [
    /*
    |--------------------------------------------------------------------------
    | Informations de l'Entreprise
    |--------------------------------------------------------------------------
    |
    | Ces informations sont utilisées dans toute l'application
    |
    */

    'name' => env('COMPANY_NAME', 'Rénovation Expert'),
    'legal_name' => env('COMPANY_LEGAL_NAME', 'Rénovation Expert SARL'),
    'slogan' => env('COMPANY_SLOGAN', 'Expert en travaux de toiture, façade et isolation'),
    'description' => env('COMPANY_DESCRIPTION', 'Depuis plus de 20 ans, Rénovation Expert accompagne les particuliers et les professionnels dans leurs projets de rénovation. Spécialisés dans les travaux de toiture, façade et isolation, nous mettons notre expertise et notre savoir-faire au service de votre confort et de la valorisation de votre patrimoine.'),
    'short_description' => env('COMPANY_SHORT_DESCRIPTION', 'Entreprise spécialisée en travaux de rénovation : toiture, façade, isolation. Certification RGE, garantie décennale, +20 ans d\'expérience.'),
    
    'phone' => env('COMPANY_PHONE', '01 23 45 67 89'),
    'phone_raw' => env('COMPANY_PHONE_RAW', '0123456789'),
    'email' => env('COMPANY_EMAIL', 'contact@renovation-expert.fr'),
    'website' => env('COMPANY_WEBSITE', 'https://www.renovation-expert.fr'),
    
    'address' => [
        'street' => env('COMPANY_ADDRESS_STREET', '123 Avenue des Travaux'),
        'city' => env('COMPANY_ADDRESS_CITY', 'Paris'),
        'postal_code' => env('COMPANY_ADDRESS_POSTAL_CODE', '75001'),
        'country' => env('COMPANY_ADDRESS_COUNTRY', 'France'),
    ],
    
    'siret' => env('COMPANY_SIRET', '123 456 789 00012'),
    'tva' => env('COMPANY_TVA', 'FR 12 345678901'),
    
    'certifications' => [
        'RGE' => true,
        'Qualibat' => true,
        'Garantie décennale' => true,
    ],
    
    'hours' => [
        'weekdays' => 'Lundi - Vendredi: 8h00 - 18h00',
        'saturday' => 'Samedi: 9h00 - 12h00',
        'sunday' => 'Fermé',
    ],
    
    'social' => [
        'facebook' => env('COMPANY_FACEBOOK', 'https://www.facebook.com/renovationexpert'),
        'instagram' => env('COMPANY_INSTAGRAM', 'https://www.instagram.com/renovationexpert'),
        'linkedin' => env('COMPANY_LINKEDIN', 'https://www.linkedin.com/company/renovationexpert'),
    ],
    
    'stats' => [
        'years_experience' => 20,
        'projects_completed' => 5000,
        'satisfied_customers' => 4800,
        'team_members' => 25,
    ],
    
    'services' => [
        'toiture' => [
            'name' => 'Travaux de Toiture',
            'description' => 'Rénovation, réparation et entretien de tous types de toitures',
            'icon' => 'icons2/Toiture/Toiture.webp',
        ],
        'facade' => [
            'name' => 'Travaux de Façade',
            'description' => 'Ravalement, nettoyage et isolation des façades',
            'icon' => 'icons2/Facade.webp',
        ],
        'isolation' => [
            'name' => 'Isolation Thermique',
            'description' => 'Isolation des combles, murs et sols pour économiser l\'énergie',
            'icon' => 'icons2/Isolation.webp',
        ],
    ],
    
    'google' => [
        'rating' => 4.9,
        'total_reviews' => 156,
        'place_id' => env('GOOGLE_PLACE_ID', ''),
    ],
];


return [
    /*
    |--------------------------------------------------------------------------
    | Informations de l'Entreprise
    |--------------------------------------------------------------------------
    |
    | Ces informations sont utilisées dans toute l'application
    |
    */

    'name' => env('COMPANY_NAME', 'Rénovation Expert'),
    'legal_name' => env('COMPANY_LEGAL_NAME', 'Rénovation Expert SARL'),
    'slogan' => env('COMPANY_SLOGAN', 'Expert en travaux de toiture, façade et isolation'),
    'description' => env('COMPANY_DESCRIPTION', 'Depuis plus de 20 ans, Rénovation Expert accompagne les particuliers et les professionnels dans leurs projets de rénovation. Spécialisés dans les travaux de toiture, façade et isolation, nous mettons notre expertise et notre savoir-faire au service de votre confort et de la valorisation de votre patrimoine.'),
    'short_description' => env('COMPANY_SHORT_DESCRIPTION', 'Entreprise spécialisée en travaux de rénovation : toiture, façade, isolation. Certification RGE, garantie décennale, +20 ans d\'expérience.'),
    
    'phone' => env('COMPANY_PHONE', '01 23 45 67 89'),
    'phone_raw' => env('COMPANY_PHONE_RAW', '0123456789'),
    'email' => env('COMPANY_EMAIL', 'contact@renovation-expert.fr'),
    'website' => env('COMPANY_WEBSITE', 'https://www.renovation-expert.fr'),
    
    'address' => [
        'street' => env('COMPANY_ADDRESS_STREET', '123 Avenue des Travaux'),
        'city' => env('COMPANY_ADDRESS_CITY', 'Paris'),
        'postal_code' => env('COMPANY_ADDRESS_POSTAL_CODE', '75001'),
        'country' => env('COMPANY_ADDRESS_COUNTRY', 'France'),
    ],
    
    'siret' => env('COMPANY_SIRET', '123 456 789 00012'),
    'tva' => env('COMPANY_TVA', 'FR 12 345678901'),
    
    'certifications' => [
        'RGE' => true,
        'Qualibat' => true,
        'Garantie décennale' => true,
    ],
    
    'hours' => [
        'weekdays' => 'Lundi - Vendredi: 8h00 - 18h00',
        'saturday' => 'Samedi: 9h00 - 12h00',
        'sunday' => 'Fermé',
    ],
    
    'social' => [
        'facebook' => env('COMPANY_FACEBOOK', 'https://www.facebook.com/renovationexpert'),
        'instagram' => env('COMPANY_INSTAGRAM', 'https://www.instagram.com/renovationexpert'),
        'linkedin' => env('COMPANY_LINKEDIN', 'https://www.linkedin.com/company/renovationexpert'),
    ],
    
    'stats' => [
        'years_experience' => 20,
        'projects_completed' => 5000,
        'satisfied_customers' => 4800,
        'team_members' => 25,
    ],
    
    'services' => [
        'toiture' => [
            'name' => 'Travaux de Toiture',
            'description' => 'Rénovation, réparation et entretien de tous types de toitures',
            'icon' => 'icons2/Toiture/Toiture.webp',
        ],
        'facade' => [
            'name' => 'Travaux de Façade',
            'description' => 'Ravalement, nettoyage et isolation des façades',
            'icon' => 'icons2/Facade.webp',
        ],
        'isolation' => [
            'name' => 'Isolation Thermique',
            'description' => 'Isolation des combles, murs et sols pour économiser l\'énergie',
            'icon' => 'icons2/Isolation.webp',
        ],
    ],
    
    'google' => [
        'rating' => 4.9,
        'total_reviews' => 156,
        'place_id' => env('GOOGLE_PLACE_ID', ''),
    ],
];


return [
    /*
    |--------------------------------------------------------------------------
    | Informations de l'Entreprise
    |--------------------------------------------------------------------------
    |
    | Ces informations sont utilisées dans toute l'application
    |
    */

    'name' => env('COMPANY_NAME', 'Rénovation Expert'),
    'legal_name' => env('COMPANY_LEGAL_NAME', 'Rénovation Expert SARL'),
    'slogan' => env('COMPANY_SLOGAN', 'Expert en travaux de toiture, façade et isolation'),
    'description' => env('COMPANY_DESCRIPTION', 'Depuis plus de 20 ans, Rénovation Expert accompagne les particuliers et les professionnels dans leurs projets de rénovation. Spécialisés dans les travaux de toiture, façade et isolation, nous mettons notre expertise et notre savoir-faire au service de votre confort et de la valorisation de votre patrimoine.'),
    'short_description' => env('COMPANY_SHORT_DESCRIPTION', 'Entreprise spécialisée en travaux de rénovation : toiture, façade, isolation. Certification RGE, garantie décennale, +20 ans d\'expérience.'),
    
    'phone' => env('COMPANY_PHONE', '01 23 45 67 89'),
    'phone_raw' => env('COMPANY_PHONE_RAW', '0123456789'),
    'email' => env('COMPANY_EMAIL', 'contact@renovation-expert.fr'),
    'website' => env('COMPANY_WEBSITE', 'https://www.renovation-expert.fr'),
    
    'address' => [
        'street' => env('COMPANY_ADDRESS_STREET', '123 Avenue des Travaux'),
        'city' => env('COMPANY_ADDRESS_CITY', 'Paris'),
        'postal_code' => env('COMPANY_ADDRESS_POSTAL_CODE', '75001'),
        'country' => env('COMPANY_ADDRESS_COUNTRY', 'France'),
    ],
    
    'siret' => env('COMPANY_SIRET', '123 456 789 00012'),
    'tva' => env('COMPANY_TVA', 'FR 12 345678901'),
    
    'certifications' => [
        'RGE' => true,
        'Qualibat' => true,
        'Garantie décennale' => true,
    ],
    
    'hours' => [
        'weekdays' => 'Lundi - Vendredi: 8h00 - 18h00',
        'saturday' => 'Samedi: 9h00 - 12h00',
        'sunday' => 'Fermé',
    ],
    
    'social' => [
        'facebook' => env('COMPANY_FACEBOOK', 'https://www.facebook.com/renovationexpert'),
        'instagram' => env('COMPANY_INSTAGRAM', 'https://www.instagram.com/renovationexpert'),
        'linkedin' => env('COMPANY_LINKEDIN', 'https://www.linkedin.com/company/renovationexpert'),
    ],
    
    'stats' => [
        'years_experience' => 20,
        'projects_completed' => 5000,
        'satisfied_customers' => 4800,
        'team_members' => 25,
    ],
    
    'services' => [
        'toiture' => [
            'name' => 'Travaux de Toiture',
            'description' => 'Rénovation, réparation et entretien de tous types de toitures',
            'icon' => 'icons2/Toiture/Toiture.webp',
        ],
        'facade' => [
            'name' => 'Travaux de Façade',
            'description' => 'Ravalement, nettoyage et isolation des façades',
            'icon' => 'icons2/Facade.webp',
        ],
        'isolation' => [
            'name' => 'Isolation Thermique',
            'description' => 'Isolation des combles, murs et sols pour économiser l\'énergie',
            'icon' => 'icons2/Isolation.webp',
        ],
    ],
    
    'google' => [
        'rating' => 4.9,
        'total_reviews' => 156,
        'place_id' => env('GOOGLE_PLACE_ID', ''),
    ],
];


return [
    /*
    |--------------------------------------------------------------------------
    | Informations de l'Entreprise
    |--------------------------------------------------------------------------
    |
    | Ces informations sont utilisées dans toute l'application
    |
    */

    'name' => env('COMPANY_NAME', 'Rénovation Expert'),
    'legal_name' => env('COMPANY_LEGAL_NAME', 'Rénovation Expert SARL'),
    'slogan' => env('COMPANY_SLOGAN', 'Expert en travaux de toiture, façade et isolation'),
    'description' => env('COMPANY_DESCRIPTION', 'Depuis plus de 20 ans, Rénovation Expert accompagne les particuliers et les professionnels dans leurs projets de rénovation. Spécialisés dans les travaux de toiture, façade et isolation, nous mettons notre expertise et notre savoir-faire au service de votre confort et de la valorisation de votre patrimoine.'),
    'short_description' => env('COMPANY_SHORT_DESCRIPTION', 'Entreprise spécialisée en travaux de rénovation : toiture, façade, isolation. Certification RGE, garantie décennale, +20 ans d\'expérience.'),
    
    'phone' => env('COMPANY_PHONE', '01 23 45 67 89'),
    'phone_raw' => env('COMPANY_PHONE_RAW', '0123456789'),
    'email' => env('COMPANY_EMAIL', 'contact@renovation-expert.fr'),
    'website' => env('COMPANY_WEBSITE', 'https://www.renovation-expert.fr'),
    
    'address' => [
        'street' => env('COMPANY_ADDRESS_STREET', '123 Avenue des Travaux'),
        'city' => env('COMPANY_ADDRESS_CITY', 'Paris'),
        'postal_code' => env('COMPANY_ADDRESS_POSTAL_CODE', '75001'),
        'country' => env('COMPANY_ADDRESS_COUNTRY', 'France'),
    ],
    
    'siret' => env('COMPANY_SIRET', '123 456 789 00012'),
    'tva' => env('COMPANY_TVA', 'FR 12 345678901'),
    
    'certifications' => [
        'RGE' => true,
        'Qualibat' => true,
        'Garantie décennale' => true,
    ],
    
    'hours' => [
        'weekdays' => 'Lundi - Vendredi: 8h00 - 18h00',
        'saturday' => 'Samedi: 9h00 - 12h00',
        'sunday' => 'Fermé',
    ],
    
    'social' => [
        'facebook' => env('COMPANY_FACEBOOK', 'https://www.facebook.com/renovationexpert'),
        'instagram' => env('COMPANY_INSTAGRAM', 'https://www.instagram.com/renovationexpert'),
        'linkedin' => env('COMPANY_LINKEDIN', 'https://www.linkedin.com/company/renovationexpert'),
    ],
    
    'stats' => [
        'years_experience' => 20,
        'projects_completed' => 5000,
        'satisfied_customers' => 4800,
        'team_members' => 25,
    ],
    
    'services' => [
        'toiture' => [
            'name' => 'Travaux de Toiture',
            'description' => 'Rénovation, réparation et entretien de tous types de toitures',
            'icon' => 'icons2/Toiture/Toiture.webp',
        ],
        'facade' => [
            'name' => 'Travaux de Façade',
            'description' => 'Ravalement, nettoyage et isolation des façades',
            'icon' => 'icons2/Facade.webp',
        ],
        'isolation' => [
            'name' => 'Isolation Thermique',
            'description' => 'Isolation des combles, murs et sols pour économiser l\'énergie',
            'icon' => 'icons2/Isolation.webp',
        ],
    ],
    
    'google' => [
        'rating' => 4.9,
        'total_reviews' => 156,
        'place_id' => env('GOOGLE_PLACE_ID', ''),
    ],
];


return [
    /*
    |--------------------------------------------------------------------------
    | Informations de l'Entreprise
    |--------------------------------------------------------------------------
    |
    | Ces informations sont utilisées dans toute l'application
    |
    */

    'name' => env('COMPANY_NAME', 'Rénovation Expert'),
    'legal_name' => env('COMPANY_LEGAL_NAME', 'Rénovation Expert SARL'),
    'slogan' => env('COMPANY_SLOGAN', 'Expert en travaux de toiture, façade et isolation'),
    'description' => env('COMPANY_DESCRIPTION', 'Depuis plus de 20 ans, Rénovation Expert accompagne les particuliers et les professionnels dans leurs projets de rénovation. Spécialisés dans les travaux de toiture, façade et isolation, nous mettons notre expertise et notre savoir-faire au service de votre confort et de la valorisation de votre patrimoine.'),
    'short_description' => env('COMPANY_SHORT_DESCRIPTION', 'Entreprise spécialisée en travaux de rénovation : toiture, façade, isolation. Certification RGE, garantie décennale, +20 ans d\'expérience.'),
    
    'phone' => env('COMPANY_PHONE', '01 23 45 67 89'),
    'phone_raw' => env('COMPANY_PHONE_RAW', '0123456789'),
    'email' => env('COMPANY_EMAIL', 'contact@renovation-expert.fr'),
    'website' => env('COMPANY_WEBSITE', 'https://www.renovation-expert.fr'),
    
    'address' => [
        'street' => env('COMPANY_ADDRESS_STREET', '123 Avenue des Travaux'),
        'city' => env('COMPANY_ADDRESS_CITY', 'Paris'),
        'postal_code' => env('COMPANY_ADDRESS_POSTAL_CODE', '75001'),
        'country' => env('COMPANY_ADDRESS_COUNTRY', 'France'),
    ],
    
    'siret' => env('COMPANY_SIRET', '123 456 789 00012'),
    'tva' => env('COMPANY_TVA', 'FR 12 345678901'),
    
    'certifications' => [
        'RGE' => true,
        'Qualibat' => true,
        'Garantie décennale' => true,
    ],
    
    'hours' => [
        'weekdays' => 'Lundi - Vendredi: 8h00 - 18h00',
        'saturday' => 'Samedi: 9h00 - 12h00',
        'sunday' => 'Fermé',
    ],
    
    'social' => [
        'facebook' => env('COMPANY_FACEBOOK', 'https://www.facebook.com/renovationexpert'),
        'instagram' => env('COMPANY_INSTAGRAM', 'https://www.instagram.com/renovationexpert'),
        'linkedin' => env('COMPANY_LINKEDIN', 'https://www.linkedin.com/company/renovationexpert'),
    ],
    
    'stats' => [
        'years_experience' => 20,
        'projects_completed' => 5000,
        'satisfied_customers' => 4800,
        'team_members' => 25,
    ],
    
    'services' => [
        'toiture' => [
            'name' => 'Travaux de Toiture',
            'description' => 'Rénovation, réparation et entretien de tous types de toitures',
            'icon' => 'icons2/Toiture/Toiture.webp',
        ],
        'facade' => [
            'name' => 'Travaux de Façade',
            'description' => 'Ravalement, nettoyage et isolation des façades',
            'icon' => 'icons2/Facade.webp',
        ],
        'isolation' => [
            'name' => 'Isolation Thermique',
            'description' => 'Isolation des combles, murs et sols pour économiser l\'énergie',
            'icon' => 'icons2/Isolation.webp',
        ],
    ],
    
    'google' => [
        'rating' => 4.9,
        'total_reviews' => 156,
        'place_id' => env('GOOGLE_PLACE_ID', ''),
    ],
];


return [
    /*
    |--------------------------------------------------------------------------
    | Informations de l'Entreprise
    |--------------------------------------------------------------------------
    |
    | Ces informations sont utilisées dans toute l'application
    |
    */

    'name' => env('COMPANY_NAME', 'Rénovation Expert'),
    'legal_name' => env('COMPANY_LEGAL_NAME', 'Rénovation Expert SARL'),
    'slogan' => env('COMPANY_SLOGAN', 'Expert en travaux de toiture, façade et isolation'),
    'description' => env('COMPANY_DESCRIPTION', 'Depuis plus de 20 ans, Rénovation Expert accompagne les particuliers et les professionnels dans leurs projets de rénovation. Spécialisés dans les travaux de toiture, façade et isolation, nous mettons notre expertise et notre savoir-faire au service de votre confort et de la valorisation de votre patrimoine.'),
    'short_description' => env('COMPANY_SHORT_DESCRIPTION', 'Entreprise spécialisée en travaux de rénovation : toiture, façade, isolation. Certification RGE, garantie décennale, +20 ans d\'expérience.'),
    
    'phone' => env('COMPANY_PHONE', '01 23 45 67 89'),
    'phone_raw' => env('COMPANY_PHONE_RAW', '0123456789'),
    'email' => env('COMPANY_EMAIL', 'contact@renovation-expert.fr'),
    'website' => env('COMPANY_WEBSITE', 'https://www.renovation-expert.fr'),
    
    'address' => [
        'street' => env('COMPANY_ADDRESS_STREET', '123 Avenue des Travaux'),
        'city' => env('COMPANY_ADDRESS_CITY', 'Paris'),
        'postal_code' => env('COMPANY_ADDRESS_POSTAL_CODE', '75001'),
        'country' => env('COMPANY_ADDRESS_COUNTRY', 'France'),
    ],
    
    'siret' => env('COMPANY_SIRET', '123 456 789 00012'),
    'tva' => env('COMPANY_TVA', 'FR 12 345678901'),
    
    'certifications' => [
        'RGE' => true,
        'Qualibat' => true,
        'Garantie décennale' => true,
    ],
    
    'hours' => [
        'weekdays' => 'Lundi - Vendredi: 8h00 - 18h00',
        'saturday' => 'Samedi: 9h00 - 12h00',
        'sunday' => 'Fermé',
    ],
    
    'social' => [
        'facebook' => env('COMPANY_FACEBOOK', 'https://www.facebook.com/renovationexpert'),
        'instagram' => env('COMPANY_INSTAGRAM', 'https://www.instagram.com/renovationexpert'),
        'linkedin' => env('COMPANY_LINKEDIN', 'https://www.linkedin.com/company/renovationexpert'),
    ],
    
    'stats' => [
        'years_experience' => 20,
        'projects_completed' => 5000,
        'satisfied_customers' => 4800,
        'team_members' => 25,
    ],
    
    'services' => [
        'toiture' => [
            'name' => 'Travaux de Toiture',
            'description' => 'Rénovation, réparation et entretien de tous types de toitures',
            'icon' => 'icons2/Toiture/Toiture.webp',
        ],
        'facade' => [
            'name' => 'Travaux de Façade',
            'description' => 'Ravalement, nettoyage et isolation des façades',
            'icon' => 'icons2/Facade.webp',
        ],
        'isolation' => [
            'name' => 'Isolation Thermique',
            'description' => 'Isolation des combles, murs et sols pour économiser l\'énergie',
            'icon' => 'icons2/Isolation.webp',
        ],
    ],
    
    'google' => [
        'rating' => 4.9,
        'total_reviews' => 156,
        'place_id' => env('GOOGLE_PLACE_ID', ''),
    ],
];


return [
    /*
    |--------------------------------------------------------------------------
    | Informations de l'Entreprise
    |--------------------------------------------------------------------------
    |
    | Ces informations sont utilisées dans toute l'application
    |
    */

    'name' => env('COMPANY_NAME', 'Rénovation Expert'),
    'legal_name' => env('COMPANY_LEGAL_NAME', 'Rénovation Expert SARL'),
    'slogan' => env('COMPANY_SLOGAN', 'Expert en travaux de toiture, façade et isolation'),
    'description' => env('COMPANY_DESCRIPTION', 'Depuis plus de 20 ans, Rénovation Expert accompagne les particuliers et les professionnels dans leurs projets de rénovation. Spécialisés dans les travaux de toiture, façade et isolation, nous mettons notre expertise et notre savoir-faire au service de votre confort et de la valorisation de votre patrimoine.'),
    'short_description' => env('COMPANY_SHORT_DESCRIPTION', 'Entreprise spécialisée en travaux de rénovation : toiture, façade, isolation. Certification RGE, garantie décennale, +20 ans d\'expérience.'),
    
    'phone' => env('COMPANY_PHONE', '01 23 45 67 89'),
    'phone_raw' => env('COMPANY_PHONE_RAW', '0123456789'),
    'email' => env('COMPANY_EMAIL', 'contact@renovation-expert.fr'),
    'website' => env('COMPANY_WEBSITE', 'https://www.renovation-expert.fr'),
    
    'address' => [
        'street' => env('COMPANY_ADDRESS_STREET', '123 Avenue des Travaux'),
        'city' => env('COMPANY_ADDRESS_CITY', 'Paris'),
        'postal_code' => env('COMPANY_ADDRESS_POSTAL_CODE', '75001'),
        'country' => env('COMPANY_ADDRESS_COUNTRY', 'France'),
    ],
    
    'siret' => env('COMPANY_SIRET', '123 456 789 00012'),
    'tva' => env('COMPANY_TVA', 'FR 12 345678901'),
    
    'certifications' => [
        'RGE' => true,
        'Qualibat' => true,
        'Garantie décennale' => true,
    ],
    
    'hours' => [
        'weekdays' => 'Lundi - Vendredi: 8h00 - 18h00',
        'saturday' => 'Samedi: 9h00 - 12h00',
        'sunday' => 'Fermé',
    ],
    
    'social' => [
        'facebook' => env('COMPANY_FACEBOOK', 'https://www.facebook.com/renovationexpert'),
        'instagram' => env('COMPANY_INSTAGRAM', 'https://www.instagram.com/renovationexpert'),
        'linkedin' => env('COMPANY_LINKEDIN', 'https://www.linkedin.com/company/renovationexpert'),
    ],
    
    'stats' => [
        'years_experience' => 20,
        'projects_completed' => 5000,
        'satisfied_customers' => 4800,
        'team_members' => 25,
    ],
    
    'services' => [
        'toiture' => [
            'name' => 'Travaux de Toiture',
            'description' => 'Rénovation, réparation et entretien de tous types de toitures',
            'icon' => 'icons2/Toiture/Toiture.webp',
        ],
        'facade' => [
            'name' => 'Travaux de Façade',
            'description' => 'Ravalement, nettoyage et isolation des façades',
            'icon' => 'icons2/Facade.webp',
        ],
        'isolation' => [
            'name' => 'Isolation Thermique',
            'description' => 'Isolation des combles, murs et sols pour économiser l\'énergie',
            'icon' => 'icons2/Isolation.webp',
        ],
    ],
    
    'google' => [
        'rating' => 4.9,
        'total_reviews' => 156,
        'place_id' => env('GOOGLE_PLACE_ID', ''),
    ],
];

