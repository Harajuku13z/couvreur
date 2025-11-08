<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offres d'emploi - Normes Rénovation Bretagne</title>
    <meta name="description" content="Découvrez nos offres d'emploi dans le domaine de la couverture et de la rénovation en Bretagne. Rejoignez notre équipe de professionnels.">
    <meta name="robots" content="index, follow">
    
    <!-- Données structurées JSON-LD pour chaque offre d'emploi -->
    @if(isset($jobs) && is_array($jobs))
    @foreach($jobs as $job)
    <script type="application/ld+json">
    {!! json_encode([
        "@context" => "https://schema.org",
        "@type" => "JobPosting",
        "title" => $job['title'],
        "description" => $job['description'],
        "identifier" => [
            "@type" => "PropertyValue",
            "name" => "Normes Rénovation Bretagne",
            "value" => (string)$job['id']
        ],
        "datePosted" => $job['datePosted'],
        "validThrough" => $job['validThrough'],
        "employmentType" => $job['employmentType'],
        "hiringOrganization" => [
            "@type" => "Organization",
            "name" => $job['hiringOrganization']['name'],
            "sameAs" => $job['hiringOrganization']['sameAs']
        ],
        "jobLocation" => [
            "@type" => "Place",
            "address" => [
                "@type" => "PostalAddress",
                "addressLocality" => $job['jobLocation']['address']['addressLocality'],
                "addressRegion" => $job['jobLocation']['address']['addressRegion'],
                "addressCountry" => $job['jobLocation']['address']['addressCountry']
            ]
        ],
        "baseSalary" => [
            "@type" => "MonetaryAmount",
            "currency" => $job['baseSalary']['currency'],
            "value" => [
                "@type" => "QuantitativeValue",
                "minValue" => $job['baseSalary']['value']['minValue'],
                "maxValue" => $job['baseSalary']['value']['maxValue'],
                "unitText" => "MONTH"
            ]
        ],
        "workHours" => $job['workHours'],
        "qualifications" => $job['qualifications']
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
    @endforeach
    @endif
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f5f5f5;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        header {
            text-align: center;
            margin-bottom: 50px;
        }
        
        h1 {
            font-size: 2.5rem;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .subtitle {
            font-size: 1.2rem;
            color: #7f8c8d;
        }
        
        .jobs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        
        .job-card {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .job-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .job-title {
            font-size: 1.5rem;
            color: #2c3e50;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .job-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
            font-size: 0.9rem;
            color: #7f8c8d;
        }
        
        .job-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .job-description {
            margin-bottom: 20px;
            line-height: 1.8;
            color: #555;
        }
        
        .job-salary {
            font-size: 1.1rem;
            font-weight: 600;
            color: #27ae60;
            margin-bottom: 15px;
        }
        
        .job-details {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ecf0f1;
        }
        
        .job-details h3 {
            font-size: 1rem;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .job-details p {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .badge-full-time {
            background: #e8f5e9;
            color: #2e7d32;
        }
        
        .badge-part-time {
            background: #fff3e0;
            color: #e65100;
        }
        
        @media (max-width: 768px) {
            h1 {
                font-size: 2rem;
            }
            
            .jobs-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Offres d'emploi</h1>
            <p class="subtitle">Rejoignez notre équipe de professionnels de la couverture et de la rénovation</p>
        </header>
        
        <div class="jobs-grid">
            @if(isset($jobs) && is_array($jobs) && count($jobs) > 0)
            @foreach($jobs as $job)
            <article class="job-card">
                <h2 class="job-title">{{ $job['title'] }}</h2>
                
                <div class="job-meta">
                    <span>
                        <span class="badge badge-{{ strtolower(str_replace('_', '-', $job['employmentType'])) }}">
                            {{ $job['employmentType'] === 'FULL_TIME' ? 'Temps plein' : 'Temps partiel' }}
                        </span>
                    </span>
                    <span>📍 {{ $job['jobLocation']['address']['addressLocality'] }}</span>
                    <span>📅 Publié le {{ date('d/m/Y', strtotime($job['datePosted'])) }}</span>
                </div>
                
                <p class="job-description">{!! nl2br(e($job['description'])) !!}</p>
                
                <div class="job-salary">
                    {{ number_format($job['baseSalary']['value']['minValue'], 0, ',', ' ') }}€ - {{ number_format($job['baseSalary']['value']['maxValue'], 0, ',', ' ') }}€ / mois
                </div>
                
                <div class="job-details">
                    <h3>Horaires</h3>
                    <p>{{ $job['workHours'] }}</p>
                    
                    <h3 style="margin-top: 15px;">Qualifications requises</h3>
                    <p>{!! nl2br(e($job['qualifications'])) !!}</p>
                </div>
            </article>
            @endforeach
            @else
            <div class="job-card">
                <p>Aucune offre d'emploi disponible pour le moment.</p>
            </div>
            @endif
        </div>
    </div>
</body>
</html>

