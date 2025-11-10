@extends('layouts.app')

@section('title', $article->meta_title ?: $article->title)
@section('description', $article->meta_description)
@section('keywords', $article->meta_keywords)

@push('head')
<style>
    :root {
        --primary-color: {{ setting('primary_color', '#3b82f6') }};
        --secondary-color: {{ setting('secondary_color', '#1e40af') }};
        --accent-color: {{ setting('accent_color', '#f59e0b') }};
    }
</style>
<!-- Styles pour le contenu généré par ChatGPT avec Tailwind CSS -->
<style>
/* Styles pour le contenu généré par ChatGPT avec Tailwind CSS */
.article-content {
    line-height: 1.8;
    color: #374151;
    font-size: 1.1rem;
}

.article-content .text-link {
    color: var(--primary-color, #3b82f6);
    text-decoration: underline;
    word-break: break-all;
}

.article-content .text-link:hover {
    color: var(--secondary-color, #1e40af);
    text-decoration: none;
}

/* S'assurer que le contenu Tailwind s'affiche correctement */
.article-content .max-w-7xl {
    max-width: 80rem;
}

.article-content .text-4xl {
    font-size: 2.25rem;
    line-height: 2.5rem;
}

.article-content .text-2xl {
    font-size: 1.5rem;
    line-height: 2rem;
}

.article-content .text-xl {
    font-size: 1.25rem;
    line-height: 1.75rem;
}

.article-content .bg-white {
    background-color: #ffffff;
}

.article-content .bg-green-50 {
    background-color: #f0fdf4;
}

.article-content .bg-blue-50 {
    background-color: #eff6ff;
}

.article-content .rounded-xl {
    border-radius: 0.75rem;
}

.article-content .shadow {
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
}

.article-content .hover\:shadow-lg:hover {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.article-content .transition {
    transition-property: color, background-color, border-color, text-decoration-color, fill, stroke;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

.article-content .duration-300 {
    transition-duration: 300ms;
}

.article-content .text-gray-900 {
    color: #111827;
}

.article-content .text-gray-800 {
    color: #1f2937;
}

.article-content .text-gray-700 {
    color: #374151;
}

.article-content .text-blue-500 {
    color: #3b82f6;
}

.article-content .text-white {
    color: #ffffff;
}

.article-content .bg-blue-500 {
    background-color: #3b82f6;
}

.article-content .hover\:bg-blue-600:hover {
    background-color: #2563eb;
}

.article-content .font-bold {
    font-weight: 700;
}

.article-content .font-semibold {
    font-weight: 600;
}

.article-content .mb-2 {
    margin-bottom: 0.5rem;
}

.article-content .mb-4 {
    margin-bottom: 1rem;
}

.article-content .mb-6 {
    margin-bottom: 1.5rem;
}

.article-content .my-4 {
    margin-top: 1rem;
    margin-bottom: 1rem;
}

.article-content .p-4 {
    padding: 1rem;
}

.article-content .p-6 {
    padding: 1.5rem;
}

.article-content .px-6 {
    padding-left: 1.5rem;
    padding-right: 1.5rem;
}

.article-content .py-3 {
    padding-top: 0.75rem;
    padding-bottom: 0.75rem;
}

.article-content .rounded-lg {
    border-radius: 0.5rem;
}

.article-content .inline-block {
    display: inline-block;
}

.article-content .text-center {
    text-align: center;
}

.article-content .list-disc {
    list-style-type: disc;
}

.article-content .list-inside {
    list-style-position: inside;
}

/* Styles pour les titres et éléments HTML de base */
.article-content h1 {
    font-size: 2.25rem;
    line-height: 2.5rem;
    font-weight: 700;
    color: #111827;
    margin-top: 1.5rem;
    margin-bottom: 1rem;
}

.article-content h2 {
    font-size: 2rem;
    line-height: 2.5rem;
    font-weight: 700;
    color: #111827;
    margin-top: 2.5rem;
    margin-bottom: 1.25rem;
    padding-bottom: 0.75rem;
    border-bottom: 3px solid var(--primary-color, #3b82f6);
}

.article-content h3 {
    font-size: 1.5rem;
    line-height: 2rem;
    font-weight: 600;
    color: #1f2937;
    margin-top: 2rem;
    margin-bottom: 1rem;
    padding-left: 0.75rem;
    border-left: 4px solid var(--accent-color, #f59e0b);
}

.article-content h4 {
    font-size: 1.25rem;
    line-height: 1.75rem;
    font-weight: 600;
    color: #374151;
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
}

.article-content p {
    margin-bottom: 1.25rem;
    line-height: 1.8;
    color: #374151;
    text-align: justify;
}

.article-content br {
    display: block;
    content: "";
    margin-top: 0.5rem;
}

.article-content ul,
.article-content ol {
    margin-bottom: 1rem;
    padding-left: 1.5rem;
}

.article-content ul.list-icon {
    list-style: none;
    padding-left: 0;
    margin: 1.5rem 0;
}

.article-content ul.list-icon li {
    position: relative;
    padding-left: 2rem;
    margin-bottom: 0.75rem;
    line-height: 1.75;
}

.article-content ul.list-icon li::before {
    content: "✓";
    position: absolute;
    left: 0;
    color: var(--primary-color, #3b82f6);
    font-weight: bold;
    font-size: 1.2rem;
}

.article-content ul:not(.list-icon) {
    list-style-type: disc;
    margin: 1.5rem 0;
    padding-left: 2rem;
}

.article-content ol {
    list-style-type: decimal;
    margin: 1.5rem 0;
    padding-left: 2rem;
}

.article-content li {
    margin-bottom: 0.75rem;
    line-height: 1.8;
}

.article-content strong {
    font-weight: 700;
    color: #111827;
}

.article-content em {
    font-style: italic;
}

.article-content a {
    color: #3b82f6;
    text-decoration: underline;
}

.article-content a:hover {
    color: #2563eb;
}

.article-content img {
    max-width: 100%;
    height: auto;
    border-radius: 0.5rem;
    margin: 1.5rem 0;
}

.article-content blockquote {
    border-left: 4px solid #3b82f6;
    padding-left: 1rem;
    margin: 1.5rem 0;
    font-style: italic;
    color: #6b7280;
}

.article-content code {
    background-color: #f3f4f6;
    padding: 0.125rem 0.375rem;
    border-radius: 0.25rem;
    font-family: monospace;
    font-size: 0.875em;
}

.article-content pre {
    background-color: #1f2937;
    color: #f9fafb;
    padding: 1rem;
    border-radius: 0.5rem;
    overflow-x: auto;
    margin: 1.5rem 0;
}

.article-content pre code {
    background-color: transparent;
    padding: 0;
    color: inherit;
}

.article-content hr {
    border: none;
    border-top: 2px solid #e5e7eb;
    margin: 2rem 0;
}

/* Styles pour la FAQ - CRITIQUE pour préserver le HTML */
.article-content #faq {
    margin-top: 3rem;
    margin-bottom: 3rem;
    padding: 2rem;
    background-color: #f9fafb;
    border-radius: 0.75rem;
    border: 1px solid #e5e7eb;
}

.article-content #faq h2 {
    font-size: 1.875rem;
    font-weight: 700;
    color: #111827;
    margin-top: 0;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 3px solid var(--primary-color, #3b82f6);
}

.article-content #faq > div[itemscope] {
    margin-bottom: 2rem;
    padding: 1.5rem;
    background-color: #ffffff;
    border-radius: 0.5rem;
    border-left: 4px solid var(--primary-color, #3b82f6);
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}

.article-content #faq h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
    margin-top: 0;
    margin-bottom: 1rem;
    padding-left: 0;
    border-left: none;
}

.article-content #faq div[itemscope][itemprop="acceptedAnswer"] {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

.article-content #faq div[itemscope][itemprop="acceptedAnswer"] p {
    margin-bottom: 0.5rem;
    color: #374151;
    line-height: 1.75;
}

.article-content #faq div[itemscope][itemprop="acceptedAnswer"] p:last-child {
    margin-bottom: 0;
}

/* Styles pour les sections FAQ avec divs imbriqués */
.article-content #faq section {
    margin-bottom: 2rem;
}

.article-content #faq section > div {
    margin-bottom: 1.5rem;
}

/* Styles pour le CTA final */
.article-content .cta-final {
    margin-top: 3rem;
    margin-bottom: 3rem;
    padding: 2.5rem;
    background: linear-gradient(135deg, var(--primary-color, #3b82f6) 0%, var(--secondary-color, #1e40af) 100%);
    border-radius: 1rem;
    color: #ffffff;
    text-align: center;
}

.article-content .cta-final h3 {
    font-size: 1.875rem;
    font-weight: 700;
    color: #ffffff;
    margin-top: 0;
    margin-bottom: 1.5rem;
    padding-left: 0;
    border-left: none;
}

.article-content .cta-final p {
    color: #ffffff;
    margin-bottom: 1.5rem;
}

.article-content .cta-final ul {
    text-align: left;
    display: inline-block;
    margin-bottom: 2rem;
    color: #ffffff;
}

.article-content .cta-final li {
    margin-bottom: 0.75rem;
    color: #ffffff;
}

.article-content .cta-buttons {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    align-items: center;
}

@media (min-width: 640px) {
    .article-content .cta-buttons {
        flex-direction: row;
        justify-content: center;
    }
}

.article-content .cta-buttons a {
    display: inline-block;
    padding: 1rem 2rem;
    border-radius: 0.5rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

.article-content .cta-buttons .btn-primary {
    background-color: #ffffff;
    color: var(--primary-color, #3b82f6);
}

.article-content .cta-buttons .btn-primary:hover {
    background-color: #f3f4f6;
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.article-content .cta-buttons .btn-secondary {
    background-color: rgba(255, 255, 255, 0.2);
    color: #ffffff;
    border: 2px solid #ffffff;
}

.article-content .cta-buttons .btn-secondary:hover {
    background-color: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* Styles pour la conclusion */
.article-content .article-conclusion {
    margin-top: 3rem;
    margin-bottom: 2rem;
    padding: 2rem;
    background-color: #f0f9ff;
    border-radius: 0.75rem;
    border-left: 4px solid var(--accent-color, #f59e0b);
}

.article-content .article-conclusion h2 {
    font-size: 1.75rem;
    font-weight: 700;
    color: #111827;
    margin-top: 0;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid var(--accent-color, #f59e0b);
}

.article-content .article-conclusion p {
    color: #374151;
    line-height: 1.8;
}

.article-content table {
    width: 100%;
    border-collapse: collapse;
    margin: 1.5rem 0;
}

.article-content th,
.article-content td {
    padding: 0.75rem;
    border: 1px solid #e5e7eb;
    text-align: left;
}

.article-content th {
    background-color: #f9fafb;
    font-weight: 600;
    color: #111827;
}

.article-content tr:nth-child(even) {
    background-color: #f9fafb;
}

/* Styles pour les encadrés */
.article-content .info-box,
.article-content .tip-box,
.article-content .highlight-box {
    padding: 1.5rem;
    border-radius: 0.5rem;
    margin: 1.5rem 0;
}

.article-content .info-box {
    background-color: #eff6ff;
    border-left: 4px solid #3b82f6;
}

.article-content .tip-box {
    background-color: #f0fdf4;
    border-left: 4px solid #10b981;
}

.article-content .highlight-box {
    background-color: #fef3c7;
    border-left: 4px solid #f59e0b;
}

/* Responsive */
@media (max-width: 768px) {
    .article-content {
        font-size: 1rem;
    }
    
    .article-content h1 {
        font-size: 1.875rem;
    }
    
    .article-content h2 {
        font-size: 1.5rem;
    }
    
    .article-content h3 {
        font-size: 1.25rem;
    }
    
    .article-content #faq {
        padding: 1rem;
    }
}
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-16">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">{{ $article->title }}</h1>
                <div class="flex items-center justify-center text-blue-100 space-x-4">
                    @if($article->published_at)
                    <span class="bg-blue-700 px-3 py-1 rounded-full text-sm">
                        <i class="fas fa-calendar mr-1"></i>{{ $article->published_at->format('d/m/Y') }}
                    </span>
                    @endif
                    <span class="bg-blue-700 px-3 py-1 rounded-full text-sm">
                        <i class="fas fa-clock mr-1"></i>Lecture
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-6xl mx-auto px-4 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Article Content -->
            <div class="lg:col-span-3">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    @if($article->featured_image)
                        <div class="aspect-w-16 aspect-h-9">
                            <img src="{{ asset($article->featured_image) }}" alt="{{ $article->title }}" 
                                 class="w-full h-64 object-cover">
                        </div>
                    @endif
                    
                    <div class="p-8">
                        <!-- Article Content - HTML tel quel de ChatGPT -->
                        <div class="article-content prose prose-lg max-w-none">
                            @php
                                // Le contenu est déjà en HTML depuis ChatGPT
                                // On s'assure qu'il n'y a pas d'échappement HTML
                                $content = $article->content_html;
                                
                                // Vérifier si le contenu contient des entités HTML échappées
                                // Si oui, les décoder
                                if (strpos($content, '&lt;') !== false && strpos($content, '<') === false) {
                                    $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                }
                                
                                // Générer les liens internes si le helper existe
                                if (class_exists('\App\Helpers\InternalLinkingHelper')) {
                                    try {
                                        $content = \App\Helpers\InternalLinkingHelper::generateInternalLinks($content, 'article');
                                    } catch (\Exception $e) {
                                        // Si le helper échoue, on continue avec le contenu original
                                    }
                                }
                                
                                // Convertir les URLs en liens cliquables (pour les URLs qui ne sont pas déjà dans des balises <a>)
                                $content = preg_replace_callback(
                                    '/(?<!href=["\'])(?<!>)(https?:\/\/[^\s<>"\'\)]+)(?![^<]*<\/a>)/',
                                    function($matches) {
                                        return '<a href="' . htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener noreferrer" class="text-link">' . htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8') . '</a>';
                                    },
                                    $content
                                );
                            @endphp
                            {!! $content !!}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="space-y-6">
                    <!-- Contact Card -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Besoin d'aide ?</h3>
                        <div class="space-y-4">
                            <a href="tel:{{ setting('company_phone_raw') }}" 
                               class="flex items-center text-green-600 hover:text-green-800 font-semibold">
                                <i class="fas fa-phone mr-3"></i>
                                {{ setting('company_phone') }}
                            </a>
                            <a href="{{ route('form.step', 'propertyType') }}" 
                               class="flex items-center text-blue-600 hover:text-blue-800 font-semibold">
                                <i class="fas fa-calculator mr-3"></i>
                                Devis gratuit
                            </a>
                        </div>
                    </div>

                    <!-- Company Info -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Notre Entreprise</h3>
                        <div class="space-y-3 text-sm text-gray-600">
                            <p><strong>{{ setting('company_name') }}</strong></p>
                            <p>{{ setting('company_address') }}</p>
                            <p>{{ setting('company_phone') }}</p>
                            <p>{{ setting('company_email') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="mt-12">
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white rounded-lg p-8 text-center">
                <h2 class="text-2xl font-bold mb-4">Prêt à commencer votre projet ?</h2>
                <p class="text-blue-100 mb-6">Contactez-nous pour un devis gratuit et personnalisé</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="tel:{{ setting('company_phone_raw') }}" 
                       class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                        <i class="fas fa-phone mr-2"></i>Appeler maintenant
                    </a>
                    <a href="{{ route('form.step', 'propertyType') }}" 
                       class="bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-800 transition-colors">
                        <i class="fas fa-calculator mr-2"></i>Devis gratuit
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
