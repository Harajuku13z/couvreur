@extends('layouts.app')

@push('head')
<style>
    /* Variables couleurs : héritées du layout (app.blade.php) — ne pas redéfinir :root ici (cassait le mode sombre). */
    .bg-primary { background-color: var(--primary-color); }
    .text-primary { color: var(--primary-color); }
    .border-primary { border-color: var(--primary-color); }
    .bg-secondary { background-color: var(--secondary-color); }
    .text-secondary { color: var(--secondary-color); }
    .bg-accent { background-color: var(--accent-color); }
    .text-accent { color: var(--accent-color); }
    
    .timeline-item {
        position: relative;
        padding-left: 3rem;
    }
    
    .timeline-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 2px;
        height: 100%;
        background: linear-gradient(to bottom, var(--primary-color), var(--secondary-color));
    }
    
    .timeline-item::after {
        content: '';
        position: absolute;
        left: -8px;
        top: 1rem;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: var(--primary-color);
        border: 3px solid white;
        box-shadow: 0 0 0 3px var(--primary-color);
    }
    
    .service-card {
        transition: all 0.3s ease;
    }
    
    .service-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    }
    
    /* Animation pour les partenaires */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animate-fadeIn {
        animation: fadeIn 0.3s ease-in-out;
    }
    
    /* Styles pour les logos partenaires */
    .partner-logo-container {
        transition: all 0.3s ease;
    }
    
    .partner-logo-container:hover {
        transform: scale(1.05);
    }
    
    /* Styles spécifiques pour mobile */
    @media (max-width: 768px) {
        /* Hero section mobile */
        .hero-mobile {
            min-height: 100vh !important;
            background-attachment: scroll !important;
            background-size: cover !important;
            background-position: center center !important;
            background-repeat: no-repeat !important;
            background-color: var(--secondary-color) !important;
        }
        
        /* Force background image display on mobile */
        .hero-mobile[style*="background-image"] {
            background-image: var(--hero-bg) !important;
            background-size: cover !important;
            background-position: center center !important;
            background-repeat: no-repeat !important;
            background-attachment: scroll !important;
        }
        
        /* Images responsive */
        .mobile-responsive-img {
            max-width: 100% !important;
            width: 100% !important;
            height: auto !important;
            display: block !important;
            object-fit: cover !important;
        }
        
        /* About section mobile */
        .about-image-mobile {
            width: 100% !important;
            height: auto !important;
            max-height: 400px !important;
            object-fit: cover !important;
            display: block !important;
        }
        
        /* Portfolio images mobile */
        .portfolio-image-mobile {
            width: 100% !important;
            height: 200px !important;
            object-fit: cover !important;
            display: block !important;
        }
        
        /* Service images mobile */
        .service-image-mobile {
            width: 100% !important;
            height: 200px !important;
            background-size: cover !important;
            background-position: center center !important;
            background-repeat: no-repeat !important;
            display: block !important;
        }
        
        /* Force service images to show on mobile */
        .service-image-mobile img {
            display: block !important;
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
        }
        
        /* Force image display */
        img {
            max-width: 100% !important;
            height: auto !important;
            display: block !important;
        }
    }
</style>
@endpush

@section('content')
    @php
        $homeLayout = $homeConfig['layout'] ?? 'classic';
        if (!in_array($homeLayout, ['classic', 'showcase', 'magazine', 'conversion'], true)) {
            $homeLayout = 'classic';
        }
    @endphp
    @include('home.' . $homeLayout)
@endsection
